<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    protected $fillable = ['name', 'trigger_secret', 'meta'];
    protected $casts = ['meta' => 'array'];

    public function actions(): HasMany {
        return $this->hasMany(WorkflowAction::class)->orderBy('order');
    }

    public function runs(): HasMany {
        return $this->hasMany(WorkflowRun::class)->latest();
    }
}
