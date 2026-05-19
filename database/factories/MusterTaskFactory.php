<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MusterTaskStatus;
use App\Models\Muster;
use App\Models\MusterTask;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MusterTask>
 */
class MusterTaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'muster_id' => Muster::factory(),
            'task_id' => Task::factory(),
            'status' => MusterTaskStatus::Planned,
            'notes' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => MusterTaskStatus::Completed,
        ]);
    }

    public function ongoing(): static
    {
        return $this->state(fn (): array => [
            'status' => MusterTaskStatus::Ongoing,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (): array => [
            'status' => MusterTaskStatus::Blocked,
        ]);
    }
}
