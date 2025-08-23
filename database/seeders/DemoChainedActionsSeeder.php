<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Workflow;
use App\Models\WorkflowAction;

class DemoChainedActionsSeeder extends Seeder
{
    public function run(): void
    {
        $wf = Workflow::create([
            'name'           => 'Chained Demo',
            'description'    => 'Two-step demo: Slack -> HTTP POST',
            'trigger_secret' => Str::random(28),
        ]);

        WorkflowAction::create([
            'workflow_id' => $wf->id,
            'type'        => 'slack',
            'config'      => ['channel' => '#new-channel'],
            'order'       => 1,
        ]);

        WorkflowAction::create([
            'workflow_id' => $wf->id,
            'type'        => 'http_post',
            'config'      => ['url' => 'https://httpbin.org/post'],
            'order'       => 2,
        ]);
    }
}
