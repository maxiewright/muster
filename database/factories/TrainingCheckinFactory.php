<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ConfidenceLevel;
use App\Models\TrainingCheckin;
use App\Models\TrainingGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingCheckin>
 */
class TrainingCheckinFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_goal_id' => TrainingGoal::factory(),
            'user_id' => User::factory(),
            'milestone_id' => null,
            'progress_update' => fake()->paragraph(),
            'learnings' => fake()->optional()->paragraph(),
            'blockers' => fake()->optional(0.3)->sentence(),
            'next_steps' => fake()->optional()->sentence(),
            'minutes_logged' => fake()->numberBetween(15, 240),
            'confidence_level' => fake()->randomElement(ConfidenceLevel::cases()),
        ];
    }

    public function withLearnings(): static
    {
        return $this->state(fn (array $attributes) => [
            'learnings' => fake()->paragraph(),
        ]);
    }

    public function withBlockers(): static
    {
        return $this->state(fn (array $attributes) => [
            'blockers' => fake()->sentence(),
        ]);
    }
}
