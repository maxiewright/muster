<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Mood;
use App\Models\Muster;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Muster>
 */
class MusterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-30 days', 'now'),
            'blockers' => fake()->optional(0.3)->sentence(),
            'mood' => fake()->optional()->randomElement(Mood::cases()),
        ];
    }
}
