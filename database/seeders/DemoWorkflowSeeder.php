<?php

namespace Database\Seeders;

use App\Models\Workflow;
use App\Models\WorkflowAction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        if (Workflow::count() > 0) return;

        $wf = Workflow::create([
            'name' => 'Slack Greeting',
            'trigger_secret' => Str::random(28),
            'meta' => ['description' => 'Sends a greeting to Slack'],
        ]);

        WorkflowAction::create([
            'workflow_id' => $wf->id,
            'type' => 'slack_webhook',
            'order' => 1,
            'config' => [
                'webhook_url' => env('SLACK_WEBHOOK_URL'),
                'message' => 'Hello {{name}}! Your event: {{event}}',
            ],
        ]);
    }
}
