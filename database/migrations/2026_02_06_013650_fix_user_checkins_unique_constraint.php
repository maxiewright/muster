<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BROKEN_INDEX = 'user_checkins_user_id_checkin_date_unique';

    private const CORRECT_INDEX = 'user_checkins_user_id_on_unique';

    public function up(): void
    {
        $indexListing = Schema::getIndexListing('user_checkins');

        if (in_array(self::BROKEN_INDEX, $indexListing, true)) {
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS "'.self::BROKEN_INDEX.'"');
            } else {
                Schema::table('user_checkins', function (Blueprint $table): void {
                    $table->dropUnique(self::BROKEN_INDEX);
                });
            }
        }

        if (! in_array(self::CORRECT_INDEX, $indexListing, true)) {
            Schema::table('user_checkins', function (Blueprint $table): void {
                $table->unique(['user_id', 'on']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('user_checkins', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'on']);
        });
    }
};
