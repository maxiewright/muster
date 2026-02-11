<?php

use App\Enums\TaskStatus;
use App\Events\TaskCompleted;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public TaskStatus $status;

    /** @var \Illuminate\Support\Collection<int, Task> */
    public $tasks;

    public function sortTask(mixed $taskId, mixed $position): void
    {
        DB::transaction(function () use ($taskId): void {
            $task = Task::query()->lockForUpdate()->find($taskId);

            if (! $task || $task->status === $this->status) {
                return;
            }

            if (! $task->canBeEditedBy(auth()->user())) {
                return;
            }

            $wasCompleted = $task->status === TaskStatus::Completed;
            $task->update(['status' => $this->status]);
            $this->dispatch('task-moved');

            if (! $wasCompleted && $this->status === TaskStatus::Completed) {
                TaskCompleted::dispatch($task->fresh());
            }
        });
    }

    public function render()
    {
        return view('components.task.⚡task-column.task-column');
    }
};
