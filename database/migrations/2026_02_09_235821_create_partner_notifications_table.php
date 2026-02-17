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
        Schema::create('partner_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // recipient
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('training_goal_id')->nullable()->constrained('training_goals')->cascadeOnDelete();

            $table->string('type'); // partner_request, checkin_logged, milestone_completed, verification_needed, goal_completed
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('data')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamp('actioned_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_notifications');
    }
};
