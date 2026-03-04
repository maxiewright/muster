<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TrainingCategory;
use App\Enums\TrainingGoalStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrainingGoal>
 */
class TrainingGoalFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'success_criteria' => fake()->optional()->sentence(),
            'category' => fake()->randomElement(TrainingCategory::cases()),
            'start_date' => $start,
            'target_date' => fake()->dateTimeBetween($start, '+3 months'),
            'status' => TrainingGoalStatus::Active,
            'estimated_hours' => fake()->numberBetween(10, 200),
            'is_public' => fake()->boolean(70),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TrainingGoalStatus::Draft,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TrainingGoalStatus::Active,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TrainingGoalStatus::Completed,
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TrainingGoalStatus::Verified,
            'completed_at' => now()->subWeek(),
            'verified_at' => now(),
            'progress_percentage' => 100,
        ]);
    }

    public function withPartner(?User $partner = null): static
    {
        return $this->state(fn (array $attributes) => [
            'accountability_partner_id' => $partner ?? User::factory(),
            'partner_status' => \App\Enums\PartnerStatus::Accepted,
        ]);
    }

    public function pendingPartner(?User $partner = null): static
    {
        return $this->state(fn (array $attributes) => [
            'accountability_partner_id' => $partner ?? User::factory(),
            'partner_status' => \App\Enums\PartnerStatus::Pending,
            'status' => TrainingGoalStatus::PendingPartner,
        ]);
    }
}
