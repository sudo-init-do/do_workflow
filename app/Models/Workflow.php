<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    protected $fillable = ['name', 'description', 'trigger_secret'];

    public function runs(): HasMany
    {
        return $this->hasMany(WorkflowRun::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class);
    }
}
