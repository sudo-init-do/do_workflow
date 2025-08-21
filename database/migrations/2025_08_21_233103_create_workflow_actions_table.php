<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('workflow_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // e.g. slack_webhook
            $table->unsignedInteger('order')->default(1);
            $table->json('config'); // arbitrary json per action
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('workflow_actions');
    }
};
