<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('now', '+60 days');
        $endsAt = fake()->dateTimeBetween($startsAt, $startsAt->format('Y-m-d H:i:s').' +4 hours');

        return [
            'user_id' => \App\Models\User::factory(),
            'event_type_id' => \App\Models\EventType::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'is_recurring' => fake()->boolean(20),
        ];
    }
}
