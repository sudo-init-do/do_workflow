<?php

namespace Tests\Feature;

use App\Models\Workflow;
use Database\Seeders\DemoWorkflowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TriggerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_queue_workflow_via_secret(): void
    {
        $this->seed(DemoWorkflowSeeder::class);
        $wf = Workflow::first();

        Queue::fake();

        $res = $this->postJson("/api/trigger/{$wf->trigger_secret}", [
            'name' => 'Tester',
            'event' => 'SignUp',
        ]);

        $res->assertAccepted()->assertJson([
            'message' => 'Workflow queued',
            'workflow_id' => $wf->id,
        ]);
    }
}
