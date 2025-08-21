<?php

namespace App\Jobs;

use App\Models\Workflow;
use App\Services\WorkflowRunner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExecuteWorkflowAction implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public int $workflowId,
        public array $payload = []
    ) {}

    public function handle(WorkflowRunner $runner): void
    {
        $workflow = Workflow::with('actions')->findOrFail($this->workflowId);
        $runner->run($workflow, $this->payload);
    }
}
