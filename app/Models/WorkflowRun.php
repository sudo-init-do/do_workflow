<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowRun extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_RUNNING   = 'running';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_FAILED    = 'failed';

    protected $table = 'workflow_runs';

    protected $fillable = [
        'workflow_id',
        'status',
        'trigger_payload',
        'result',
        'error',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'trigger_payload' => 'array',
        'result'          => 'array',
        'started_at'      => 'datetime',
        'finished_at'     => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    // Relationships
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function workflowActionRuns(): HasMany
    {
        return $this->hasMany(WorkflowActionRun::class, 'workflow_run_id');
    }

    // Accessors
    public function getDurationSecondsAttribute(): ?int
    {
        if (! $this->started_at || ! $this->finished_at) {
            return null;
        }
        return $this->started_at->diffInSeconds($this->finished_at);
    }

    // Scopes
    public function scopeRecent($query, int $limit = 20)
    {
        return $query->latest('id')->limit($limit);
    }

    public function scopeForWorkflow($query, int|string $workflowId)
    {
        return $query->where('workflow_id', $workflowId);
    }

    public function scopeSucceeded($query)
    {
        return $query->where('status', self::STATUS_SUCCEEDED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    // State helpers
    public function markStarted(): void
    {
        $this->status     = self::STATUS_RUNNING;
        $this->started_at = now();
        $this->save();
    }

    /** @param array|null $result */
    public function markSucceeded(?array $result = null): void
    {
        $this->status      = self::STATUS_SUCCEEDED;
        $this->finished_at = now();
        if (! is_null($result)) {
            $this->result = $result;
        }
        $this->error = null;
        $this->save();
    }

    public function markFailed(\Throwable|string $error): void
    {
        $this->status      = self::STATUS_FAILED;
        $this->finished_at = now();
        $this->error       = is_string($error)
            ? $error
            : ($error->getMessage().' @ '.$error->getFile().':'.$error->getLine());
        $this->save();
    }
}
