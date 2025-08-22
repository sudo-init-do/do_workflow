<?php

namespace Database\Seeders;

use App\Models\Workflow;
use App\Models\WorkflowAction;
use Illuminate\Database\Seeder;

class DemoHttpActionSeeder extends Seeder
{
    public function run(): void
    {
        $wf = Workflow::first() ?? Workflow::factory()->create(['name' => 'Demo Workflow']);

        WorkflowAction::updateOrCreate(
            ['workflow_id' => $wf->id, 'type' => 'http_post'],
            ['config' => [
                'url' => 'https://httpbin.org/post',
                'method' => 'POST',
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'greeting' => 'Hello {{name}}!',
                    'event' => '{{event}}',
                    'workflow_id' => $wf->id,
                ],
            ]]
        );
    }
}
