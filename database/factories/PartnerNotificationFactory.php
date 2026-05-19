<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PartnerNotification;
use App\Models\TrainingGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnerNotification>
 */
class PartnerNotificationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'from_user_id' => User::factory(),
            'training_goal_id' => TrainingGoal::factory(),
            'type' => fake()->randomElement(['checkin_logged', 'milestone_completed', 'goal_completed', 'partner_request']),
            'title' => fake()->sentence(5),
            'message' => fake()->optional()->paragraph(),
            'data' => null,
            'read_at' => null,
            'actioned_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now(),
        ]);
    }

    public function actioned(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now()->subMinute(),
            'actioned_at' => now(),
        ]);
    }
}
