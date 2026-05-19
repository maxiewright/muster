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
        Schema::table('mission_memberships', function (Blueprint $table): void {
            $table->foreign('mission_id')->references('id')->on('missions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mission_memberships', function (Blueprint $table): void {
            $table->dropForeign(['mission_id']);
        });
    }
};
