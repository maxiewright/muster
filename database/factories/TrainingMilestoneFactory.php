<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MilestoneStatus;
use App\Models\TrainingGoal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrainingMilestone>
 */
class TrainingMilestoneFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_goal_id' => TrainingGoal::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'order' => fake()->numberBetween(1, 10),
            'status' => MilestoneStatus::Pending,
            'target_date' => fake()->optional()->dateTimeBetween('now', '+3 months'),
            'completed_at' => null,
            'completion_notes' => null,
            'evidence_url' => null,
            'evidence_files' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MilestoneStatus::Completed,
            'completed_at' => now(),
            'completion_notes' => fake()->sentence(),
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MilestoneStatus::Verified,
            'completed_at' => now()->subDay(),
        ]);
    }

    public function skipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MilestoneStatus::Skipped,
        ]);
    }
}
