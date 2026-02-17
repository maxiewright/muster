<?php

use App\Enums\TaskStatus;
use App\Events\TaskCompleted;
use App\Events\TaskStatusChanged;
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
            $oldStatus = $task->status;
            $task->update(['status' => $this->status]);
            $this->dispatch('task-moved');
            $actor = auth()->user();
            if ($actor instanceof \App\Models\User && $task->created_by !== $actor->id) {
                TaskStatusChanged::dispatch($task->fresh(['assignee']), $oldStatus, $this->status, $actor);
            }

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
