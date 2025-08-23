<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowActionRun extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_RUNNING   = 'running';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_FAILED    = 'failed';

    protected $table = 'workflow_action_runs';

    protected $fillable = [
        'workflow_run_id',
        'workflow_action_id',
        'attempt',
        'status',
        'result',
        'error',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'result'      => 'array',
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(WorkflowAction::class, 'workflow_action_id');
    }

    public function markStarted(): void
    {
        $this->status = self::STATUS_RUNNING;
        $this->started_at = now();
        $this->save();
    }

    public function markSucceeded(?array $result = null): void
    {
        $this->status = self::STATUS_SUCCEEDED;
        $this->finished_at = now();
        if (! is_null($result)) {
            $this->result = $result;
        }
        $this->error = null;
        $this->save();
    }

    public function markFailed(\Throwable|string $e): void
    {
        $this->status = self::STATUS_FAILED;
        $this->finished_at = now();
        $this->error = is_string($e)
            ? $e
            : ($e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
        $this->save();
    }
}
