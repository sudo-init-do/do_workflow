<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\WorkflowRun;
use App\Models\WorkflowAction;
use App\Models\WorkflowActionRun;
use App\Services\Actions\HttpPostAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Orchestrates a single WorkflowRun by executing its Actions in order,
 * honoring enabled flags, retries, and backoff. Persists per-action logs.
 */
class WorkflowRunner
{
    /**
     * @param  WorkflowRun  $run   Persisted run record (status initially 'pending')
     * @param  array        $payload  Event payload from the trigger
     */
    public function run(WorkflowRun $run, array $payload = []): void
    {
        $run->update(['status' => 'running', 'started_at' => now()]);

        /** @var Workflow $workflow */
        $workflow = $run->workflow()->with(['actions' => function ($q) {
            $q->orderBy('order');
        }])->first();

        try {
            foreach ($workflow->actions as $action) {
                // skip disabled steps
                if (! $action->enabled) {
                    $this->recordActionRun($run, $action, 'skipped', [
                        'reason' => 'disabled',
                    ]);
                    continue;
                }

                $ok = $this->executeWithRetry($run, $action, $payload);

                if (! $ok) {
                    // stop the chain on first failure
                    $run->update([
                        'status'      => 'failed',
                        'finished_at' => now(),
                    ]);
                    return;
                }
            }

            $run->update([
                'status'      => 'succeeded',
                'finished_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('WorkflowRunner fatal error', [
                'run_id'   => $run->id,
                'message'  => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            $run->update([
                'status'      => 'failed',
                'error'       => $e->getMessage(),
                'finished_at' => now(),
            ]);
        }
    }

    /**
     * Execute a single action with retry/backoff.
     */
    protected function executeWithRetry(WorkflowRun $run, WorkflowAction $action, array $payload): bool
    {
        $maxRetries     = (int) ($action->max_retries ?? 0);
        $backoffSeconds = (int) ($action->backoff_seconds ?? 10);
        $attempt        = 0;

        while (true) {
            $attempt++;

            $actionRun = $this->recordActionRun($run, $action, 'running', [
                'attempt' => $attempt,
            ]);

            try {
                $result = $this->invokeAction($action, $payload);

                // success
                $actionRun->update([
                    'status'      => 'succeeded',
                    'result'      => $result,
                    'finished_at' => now(),
                ]);

                return true;
            } catch (Throwable $e) {
                // failure on this attempt
                $actionRun->update([
                    'status'      => 'failed',
                    'error'       => $e->getMessage(),
                    'finished_at' => now(),
                ]);

                Log::warning('Action failed', [
                    'run_id'        => $run->id,
                    'action_id'     => $action->id,
                    'attempt'       => $attempt,
                    'max_retries'   => $maxRetries,
                    'backoff_sec'   => $backoffSeconds,
                    'error'         => $e->getMessage(),
                ]);

                if ($attempt > $maxRetries) {
                    // no more retries
                    return false;
                }

                // backoff (simple linear/backoff)
                sleep(max(1, $backoffSeconds));
            }
        }
    }

    /**
     * Actually perform the action based on its type.
     * Returns a normalized result array for logging.
     */
    protected function invokeAction(WorkflowAction $action, array $payload): array
    {
        $type   = $action->type;
        $config = is_array($action->config) ? $action->config : (array) $action->config;

        return match ($type) {
            'slack' => $this->doSlack($config, $payload),
            'http_post', 'http' => $this->doHttp($config, $payload),
            default => throw new \RuntimeException("Unknown action type: {$type}"),
        };
    }

    /**
     * Slack notifier via incoming webhook (already configured in services.php).
     */
    protected function doSlack(array $config, array $payload): array
    {
        $webhook = (string) config('services.slack.webhook_url');
        if (empty($webhook)) {
            throw new \RuntimeException('Slack webhook not configured (services.slack.webhook_url)');
        }

        $text = $config['text'] ?? "Hello {$payload['name']}! Your event: {$payload['event']}";
        $resp = \Illuminate\Support\Facades\Http::post($webhook, [
            'text' => $text,
        ]);

        if (! $resp->successful()) {
            throw new \RuntimeException("Slack error: HTTP {$resp->status()} - {$resp->body()}");
        }

        return ['ok' => true, 'status' => $resp->status()];
    }

    /**
     * HTTP action (delegates to HttpPostAction service for interpolation & methods).
     */
    protected function doHttp(array $config, array $payload): array
    {
        return (new HttpPostAction())($config, $payload);
    }

    /**
     * Create and return a WorkflowActionRun row with the given status & meta.
     */
    protected function recordActionRun(WorkflowRun $run, WorkflowAction $action, string $status, array $meta = []): WorkflowActionRun
    {
        return WorkflowActionRun::create([
            'workflow_run_id'    => $run->id,
            'workflow_action_id' => $action->id,
            'attempt'            => (int) ($meta['attempt'] ?? 1),
            'status'             => $status,
            'result'             => $meta['result'] ?? null,
            'error'              => $meta['error']  ?? null,
            'started_at'         => $status === 'running' ? now() : ($meta['started_at'] ?? now()),
            'finished_at'        => $status === 'running' ? null : ($meta['finished_at'] ?? null),
        ]);
    }
}
