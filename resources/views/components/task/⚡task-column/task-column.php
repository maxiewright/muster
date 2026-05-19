<?php

use App\Enums\TaskStatus;
use App\Events\TaskCompleted;
use App\Events\TaskStatusChanged;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public TaskStatus $status;

    /** @var Collection<int, Task> */
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
            if ($actor instanceof User && $task->created_by !== $actor->id) {
                event(new TaskStatusChanged($task->fresh(['assignee']), $oldStatus, $this->status, $actor));
            }

            if (! $wasCompleted && $this->status === TaskStatus::Completed) {
                event(new TaskCompleted($task->fresh()));
            }
        });
    }

    public function render(): Factory|View
    {
        return view('components.task.⚡task-column.task-column');
    }
};
