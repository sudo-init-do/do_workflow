<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_actions', function (Blueprint $table) {
            if (!Schema::hasColumn('workflow_actions', 'order')) {
                $table->unsignedSmallInteger('order')->default(1)->after('workflow_id');
            }

            if (!Schema::hasColumn('workflow_actions', 'enabled')) {
                $table->boolean('enabled')->default(true)->after('order');
            }

            if (!Schema::hasColumn('workflow_actions', 'max_retries')) {
                $table->unsignedTinyInteger('max_retries')->default(0)->after('config'); // 0 = no retry
            }

            if (!Schema::hasColumn('workflow_actions', 'backoff_seconds')) {
                $table->unsignedSmallInteger('backoff_seconds')->default(10)->after('max_retries');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workflow_actions', function (Blueprint $table) {
            foreach (['order','enabled','max_retries','backoff_seconds'] as $col) {
                if (Schema::hasColumn('workflow_actions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
