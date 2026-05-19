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
        Schema::table('training_goals', function (Blueprint $table): void {
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_unit_directed')->default(false);
            $table->boolean('accountability_partner_required')->default(false);
            $table->boolean('accountability_partner_locked')->default(false);

            $table->index('assigned_by_user_id');
            $table->index('is_unit_directed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_goals', function (Blueprint $table): void {
            $table->dropIndex(['assigned_by_user_id']);
            $table->dropIndex(['is_unit_directed']);
            $table->dropConstrainedForeignId('assigned_by_user_id');
            $table->dropColumn([
                'is_unit_directed',
                'accountability_partner_required',
                'accountability_partner_locked',
            ]);
        });
    }
};
