<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Maxie Wright',
            'email' => 'maxiewright@gmail.com',
            'role' => Role::Lead,
            'password' => bcrypt('Testing-01'), // Change this to a secure password
        ]);

        $this->call([
            EventTypeSeeder::class,
            FocusAreaSeeder::class,
            BadgeSeeder::class,
        ]);
    }
}
