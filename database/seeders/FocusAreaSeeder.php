<?php

namespace Database\Seeders;

use App\Models\FocusArea;
use Illuminate\Database\Seeder;

class FocusAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $focusAreas = [
            'laravel',
            'livewire',
            'filament',
            'php',
            'vuejs',
            'tailwindcss',
            'alpinejs',
            'databases',
            'testing',
            'devops',
        ];

        foreach ($focusAreas as $area) {
            FocusArea::create([
                'name' => $area,
            ]);
        }
    }
}
