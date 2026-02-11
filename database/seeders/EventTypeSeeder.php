<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventTypes = [
            [
                'name' => 'Huddle',
                'description' => 'A quick team meeting to discuss progress and blockers.',
                'color' => '#FF5733',
            ],
            [
                'name' => 'Training Session',
                'description' => 'An educational session to enhance team skills and knowledge.',
                'color' => '#33C1FF',
            ],
            [
                'name' => 'Pair Programming',
                'description' => 'Two developers working together on the same codebase.',
                'color' => '#75FF33',
            ],
            [
                'name' => 'Code Review',
                'description' => 'A session to review and provide feedback on code changes.',
                'color' => '#FF33A8',
            ],
        ];

        foreach ($eventTypes as $type) {
            EventType::firstOrCreate([
                'name' => $type['name'],
            ], [
                'name' => $type['name'],
                'description' => $type['description'],
                'color' => $type['color'],
            ]);
        }
    }
}
