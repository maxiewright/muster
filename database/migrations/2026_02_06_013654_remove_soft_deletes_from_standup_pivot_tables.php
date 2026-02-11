<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('standup_task', 'deleted_at')) {
            Schema::table('standup_task', function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('standup_focus_area', 'deleted_at')) {
            Schema::table('standup_focus_area', function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('standup_task', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('standup_focus_area', function (Blueprint $table): void {
            $table->softDeletes();
        });
    }
};
