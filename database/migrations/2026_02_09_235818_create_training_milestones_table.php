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
        Schema::create('training_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_goal_id')->constrained()->cascadeOnDelete();
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->default(0);
            
            // Status tracking
            $table->string('status')->default('pending');
            $table->date('target_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Evidence of completion
            $table->text('completion_notes')->nullable();
            $table->string('evidence_url')->nullable();
            $table->json('evidence_files')->nullable();
            
            // Points
            $table->unsignedInteger('points_value')->default(15);
            $table->boolean('points_awarded')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_milestones');
    }
};
