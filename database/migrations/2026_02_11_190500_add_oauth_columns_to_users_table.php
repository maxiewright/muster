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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('oauth_provider')->nullable()->after('password');
            $table->string('oauth_id')->nullable()->after('oauth_provider');

            $table->index('oauth_provider');
            $table->index('oauth_id');
            $table->unique(['oauth_provider', 'oauth_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['oauth_provider', 'oauth_id']);
            $table->dropIndex(['oauth_provider']);
            $table->dropIndex(['oauth_id']);
            $table->dropColumn(['oauth_provider', 'oauth_id']);
        });
    }
};
