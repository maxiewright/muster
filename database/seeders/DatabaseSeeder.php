<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EventTypeSeeder::class,
            FocusAreaSeeder::class,
            BadgeSeeder::class,
        ]);
    }
}
