<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowRun extends Model
{
    protected $fillable = ['workflow_id', 'status', 'trigger_payload', 'result', 'error'];
    protected $casts = ['trigger_payload' => 'array', 'result' => 'array'];

    public function workflow(): BelongsTo {
        return $this->belongsTo(Workflow::class);
    }
}
