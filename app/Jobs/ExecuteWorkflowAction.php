<?php

namespace App\Jobs;

use App\Models\WorkflowAction;
use App\Models\WorkflowActionRun;
use App\Models\WorkflowRun;
use App\Services\Actions\HttpPostAction; // ⬅️ add this
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ExecuteWorkflowAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $workflowRunId,
        public array $actionPayload // ['action_id' => ..., 'attempt' => ...] or whatever you pass today
    ) {}

    public function handle(): void
    {
        $run    = WorkflowRun::findOrFail($this->workflowRunId);
        $action = WorkflowAction::findOrFail($this->actionPayload['action_id']);

        // start ActionRun row
        $actionRun = WorkflowActionRun::create([
            'workflow_run_id'   => $run->id,
            'workflow_action_id'=> $action->id,
            'attempt'           => $this->actionPayload['attempt'] ?? 1,
            'status'            => 'running',
            'started_at'        => Carbon::now(),
        ]);

        try {
            $result = match ($action->type) {
                'slack'     => $this->sendSlack($action->config, $run->trigger_payload),
                'http_post' => app(HttpPostAction::class)(
                    $action->config,                // config from DB
                    $run->trigger_payload ?? []     // event payload
                ),
                default     => throw new \RuntimeException("Unknown action type: {$action->type}"),
            };

            $actionRun->update([
                'status'      => 'succeeded',
                'result'      => $result,
                'finished_at' => Carbon::now(),
            ]);

            // (optional) mark overall run succeeded if this was the last step
            // ... your existing chaining/next-step logic here ...

        } catch (\Throwable $e) {
            $actionRun->update([
                'status'      => 'failed',
                'error'       => $e->getMessage(),
                'finished_at' => Carbon::now(),
            ]);

            // bubble failure to the parent run if that’s your design
            $run->update([
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ]);

            throw $e; // let Laravel retry if retries are configured
        }
    }

    private function sendSlack(array $config, array $payload): array
    {
        $text = $config['text'] ?? ("Hello ".$payload['name'] ?? 'there');
        $webhook = config('services.slack.webhook_url');
        $resp = \Illuminate\Support\Facades\Http::post($webhook, ['text' => $text]);

        return [
            'status' => $resp->status(),
            'ok'     => $resp->successful(),
            'body'   => $resp->json() ?? $resp->body(),
        ];
    }
}
