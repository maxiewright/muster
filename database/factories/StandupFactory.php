<?php

namespace Database\Factories;

use App\Enums\Mood;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Standup>
 */
class StandupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'date' => fake()->dateTimeBetween('-30 days', 'now'),
            'blockers' => fake()->optional(0.3)->sentence(),
            'mood' => fake()->optional()->randomElement(Mood::cases()),
        ];
    }
}
