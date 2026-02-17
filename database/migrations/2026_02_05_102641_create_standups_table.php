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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->constrained('users');

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('tasks')
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Status and Priority
            $table->string('status');
            $table->string('priority');
            $table->date('due_date')->nullable();

            // Additional notes
            $table->json('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Production indexes folded into base migration
            $table->index('assigned_to');
            $table->index('created_by');
            $table->index('status');
            $table->index('priority');
            $table->index('due_date');
        });

        Schema::create('focus_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('standups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->text('blockers')->nullable();
            $table->string('mood')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'date']);
            $table->index('date');
        });

        Schema::create('standup_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standup_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->json('notes')->nullable();

            $table->timestamps();

            $table->unique(['standup_id', 'task_id']);
        });

        Schema::create('standup_focus_area', function (Blueprint $table) {
            $table->foreignId('standup_id')->constrained()->cascadeOnDelete();
            $table->foreignId('focus_area_id')->constrained()->cascadeOnDelete();
            $table->primary(['standup_id', 'focus_area_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standup_focus_area');
        Schema::dropIfExists('standup_task');
        Schema::dropIfExists('standups');
        Schema::dropIfExists('focus_areas');
        Schema::dropIfExists('tasks');
    }
};
