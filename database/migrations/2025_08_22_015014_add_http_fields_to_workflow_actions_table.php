<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workflow_actions', function (Blueprint $table) {
            // Add 'type' column if it doesn't exist
            if (! Schema::hasColumn('workflow_actions', 'type')) {
                $table->string('type')->default('slack')->after('id');
            }

            // Add 'config' JSON column if it doesn't exist
            if (! Schema::hasColumn('workflow_actions', 'config')) {
                $table->json('config')->nullable()->after('type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_actions', function (Blueprint $table) {
            if (Schema::hasColumn('workflow_actions', 'config')) {
                $table->dropColumn('config');
            }

            if (Schema::hasColumn('workflow_actions', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
