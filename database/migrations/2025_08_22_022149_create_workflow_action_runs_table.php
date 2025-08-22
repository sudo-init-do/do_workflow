<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_action_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
            $table->foreignId('workflow_action_id')->constrained('workflow_actions')->cascadeOnDelete();

            $table->unsignedSmallInteger('attempt')->default(1);
            $table->string('status')->default('pending'); // pending|running|succeeded|failed
            $table->json('result')->nullable();
            $table->text('error')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['workflow_run_id', 'workflow_action_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_action_runs');
    }
};
