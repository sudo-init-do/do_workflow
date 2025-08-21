<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowAction extends Model
{
    protected $fillable = ['workflow_id', 'type', 'order', 'config'];
    protected $casts = ['config' => 'array'];

    public function workflow(): BelongsTo {
        return $this->belongsTo(Workflow::class);
    }
}
