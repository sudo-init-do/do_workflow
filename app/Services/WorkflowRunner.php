<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\WorkflowRun;
use Illuminate\Support\Facades\Http;
use Throwable;

class WorkflowRunner
{
    public function run(Workflow $workflow, array $payload = []): WorkflowRun
    {
        $run = $workflow->runs()->create([
            'status' => 'running',
            'trigger_payload' => $payload,
        ]);

        $results = [];
        try {
            foreach ($workflow->actions as $action) {
                $results[] = $this->executeAction($action->type, $action->config, $payload);
            }
            $run->update([
                'status' => 'succeeded',
                'result' => ['steps' => $results],
            ]);
        } catch (Throwable $e) {
            $run->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }

        return $run;
    }

    protected function executeAction(string $type, array $config, array $payload): array
    {
        return match ($type) {
            'slack_webhook' => $this->sendSlack($config, $payload),
            default => throw new \RuntimeException("Unknown action type: {$type}"),
        };
    }

    protected function sendSlack(array $config, array $payload): array
    {
        $url = $config['webhook_url'] ?? config('services.slack.webhook_url');
        if (!$url) throw new \RuntimeException('Slack webhook URL missing');

        $message = $config['message'] ?? 'Workflow triggered';
        // simple templating: {{key}}
        $message = preg_replace_callback('/{{\s*(\w+)\s*}}/', fn($m) => $payload[$m[1]] ?? '', $message);

        $resp = Http::post($url, ['text' => $message]);
        if ($resp->failed()) {
            throw new \RuntimeException('Slack webhook failed: ' . $resp->body());
        }
        return ['type' => 'slack_webhook', 'ok' => true];
    }
}
