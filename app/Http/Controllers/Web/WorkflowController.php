<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use Illuminate\View\View;

class WorkflowController extends Controller
{
    public function index(): View
    {
        $workflows = Workflow::with(['runs' => fn($q) => $q->limit(10)])->get();
        return view('workflows.index', compact('workflows'));
    }

    public function show(Workflow $workflow): View
    {
        $workflow->load('actions', 'runs');
        return view('workflows.show', compact('workflow'));
    }
}
