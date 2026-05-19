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
        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreignId('mission_id')->nullable();
            $table->foreignId('action_lead_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index('mission_id');
            $table->index('action_lead_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropIndex(['mission_id']);
            $table->dropIndex(['action_lead_user_id']);
            $table->dropColumn('mission_id');
            $table->dropConstrainedForeignId('action_lead_user_id');
        });
    }
};
