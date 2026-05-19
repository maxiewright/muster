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
        Schema::create('mission_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mission_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('membership_type');
            $table->foreignId('added_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('removed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['mission_id', 'user_id']);
            $table->index('membership_type');
            $table->index('ended_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mission_memberships', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['added_by_user_id']);
            $table->dropForeign(['removed_by_user_id']);
        });

        Schema::dropIfExists('mission_memberships');
    }
};
