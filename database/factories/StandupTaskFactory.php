<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StandupTaskStatus;
use App\Models\Standup;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StandUpTask>
 */
class StandupTaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'standup_id' => Standup::factory(),
            'task_id' => Task::factory(),
            'status' => StandupTaskStatus::Planned,
            'notes' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StandupTaskStatus::Completed,
        ]);
    }

    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StandupTaskStatus::Ongoing,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StandupTaskStatus::Blocked,
        ]);
    }
}
