<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('unit_memberships')->where('role', 'owner')->update(['role' => 'commander']);
        DB::table('unit_memberships')->where('role', 'admin')->update(['role' => 'lead']);
    }

    public function down(): void
    {
        DB::table('unit_memberships')->where('role', 'commander')->update(['role' => 'owner']);
        DB::table('unit_memberships')->where('role', 'lead')->update(['role' => 'admin']);
    }
};
