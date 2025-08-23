<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowAction extends Model
{
    protected $table = 'workflow_actions';

    protected $fillable = [
        'workflow_id',
        'type',             // 'slack' | 'http_post' | ...
        'config',           // JSON config per action
        'order',            // execution order
        'enabled',          // toggle on/off
        'max_retries',      // per-step retries
        'backoff_seconds',  // delay between retries
    ];

    protected $casts = [
        'config'          => 'array',
        'enabled'         => 'bool',
        'max_retries'     => 'int',
        'backoff_seconds' => 'int',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(WorkflowActionRun::class, 'workflow_action_id');
    }
}
