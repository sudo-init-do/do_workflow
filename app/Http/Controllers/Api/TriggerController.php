<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ExecuteWorkflowAction;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TriggerController extends Controller
{
    // Trigger with secret (no auth)
    public function triggerBySecret(Request $request, string $secret)
    {
        $workflow = Workflow::where('trigger_secret', $secret)->firstOrFail();
        ExecuteWorkflowAction::dispatch($workflow->id, $request->all());

        return response()->json([
            'message' => 'Workflow queued',
            'workflow_id' => $workflow->id
        ], Response::HTTP_ACCEPTED);
    }

    // Optional: Sanctum-protected trigger
    public function triggerAuth(Request $request, Workflow $workflow)
    {
        ExecuteWorkflowAction::dispatch($workflow->id, $request->all());

        return response()->json([
            'message' => 'Workflow queued (auth)',
            'workflow_id' => $workflow->id
        ], Response::HTTP_ACCEPTED);
    }
}
