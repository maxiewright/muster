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
        Schema::create('training_goals', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            
            // Ownership
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accountability_partner_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Goal details
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('success_criteria')->nullable();
            $table->string('category')->nullable(); // technical, soft-skill, certification, project
            
            // Focus area link
            $table->foreignId('focus_area_id')->nullable()->constrained()->nullOnDelete();
            
            // Timeline
            $table->date('start_date');
            $table->date('target_date');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Status
            $table->string('status')->default('draft');
            $table->string('partner_status')->default('pending');
            $table->text('partner_decline_reason')->nullable();
            
            // Progress
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->unsignedInteger('estimated_hours')->nullable();
            $table->unsignedInteger('logged_minutes')->default(0);
            
            // Gamification
            $table->unsignedInteger('points_earned')->default(0);
            $table->unsignedInteger('base_points_value')->default(100);
            
            // Visibility
            $table->boolean('is_public')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_goals');
    }
};
