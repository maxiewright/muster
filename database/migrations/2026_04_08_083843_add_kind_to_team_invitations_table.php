<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('team_invitations', function (Blueprint $table): void {
            $table->foreignId('invited_by_user_id')->nullable()->change();
            $table->string('kind')->default('team');
            $table->index('kind');
        });

        DB::table('team_invitations')
            ->whereNull('kind')
            ->update(['kind' => 'team']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_invitations', function (Blueprint $table): void {
            $table->dropIndex(['kind']);
            $table->dropColumn('kind');
            $table->foreignId('invited_by_user_id')->nullable(false)->change();
        });
    }
};
