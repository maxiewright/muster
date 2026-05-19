<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status');
            $table->string('priority');
            $table->date('due_date')->nullable();
            $table->json('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('unit_id');
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

        Schema::create('musters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->text('blockers')->nullable();
            $table->string('mood')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('unit_id');
            $table->index('date');
            $table->unique(['user_id', 'unit_id', 'date'], 'musters_user_id_unit_id_date_unique');
        });

        Schema::create('muster_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muster_id')->constrained('musters')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->json('notes')->nullable();
            $table->timestamps();

            $table->unique(['muster_id', 'task_id'], 'muster_task_muster_id_task_id_unique');
        });

        Schema::create('muster_focus_area', function (Blueprint $table) {
            $table->foreignId('muster_id')->constrained('musters')->cascadeOnDelete();
            $table->foreignId('focus_area_id')->constrained()->cascadeOnDelete();
            $table->primary(['muster_id', 'focus_area_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('muster_focus_area');
        Schema::dropIfExists('muster_task');
        Schema::dropIfExists('musters');
        Schema::dropIfExists('focus_areas');
        Schema::dropIfExists('tasks');
    }
};
