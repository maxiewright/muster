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
        Schema::create('training_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_goal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('training_milestones')->nullOnDelete();
            
            // Check-in content
            $table->text('progress_update');
            $table->text('learnings')->nullable();
            $table->text('blockers')->nullable();
            $table->text('next_steps')->nullable();
            
            // Time tracking
            $table->unsignedInteger('minutes_logged')->default(0);
            
            // Confidence/mood
            $table->string('confidence_level')->nullable();
            
            // Partner interaction
            $table->text('partner_feedback')->nullable();
            $table->foreignId('feedback_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('feedback_at')->nullable();
            $table->string('partner_reaction')->nullable(); // encouraged, coached, celebrated, concerned
            
            // Points
            $table->unsignedInteger('points_earned')->default(5);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_checkins');
    }
};
