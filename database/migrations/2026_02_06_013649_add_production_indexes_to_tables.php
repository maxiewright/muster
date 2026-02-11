<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->index('assigned_to');
            $table->index('created_by');
            $table->index('status');
            $table->index('priority');
            $table->index('due_date');
        });

        Schema::table('standups', function (Blueprint $table): void {
            $table->index('date');
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->index('user_id');
            $table->index('starts_at');
            $table->index('ends_at');
        });

        Schema::table('point_logs', function (Blueprint $table): void {
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['due_date']);
        });

        Schema::table('standups', function (Blueprint $table): void {
            $table->dropIndex(['date']);
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['starts_at']);
            $table->dropIndex(['ends_at']);
        });

        Schema::table('point_logs', function (Blueprint $table): void {
            $table->dropIndex(['user_id']);
        });
    }
};
