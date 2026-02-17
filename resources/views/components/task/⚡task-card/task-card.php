<?php

use App\Enums\TaskStatus;
use App\Events\TaskCompleted;
use App\Events\TaskStatusChanged;
use App\Models\Task;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Task $task;

    public bool $compact = false;

    public bool $showActions = true;

    public bool $draggable = true;

    /** When true, card is used as wire:sort:handle; skip native draggable to avoid double drag visual. */
    public bool $sortHandle = false;

    #[Computed]
    public function canEdit(): bool
    {
        return $this->task->canBeEditedBy(auth()->user());
    }

    #[Computed]
    public function availableStatuses(): array
    {
        return collect(TaskStatus::cases())
            ->filter(fn ($status) => $status !== $this->task->status && $status !== TaskStatus::Backlog)
            ->values()
            ->all();
    }

    public function updateStatus(string $status): void
    {
        if (! $this->canEdit) {
            return;
        }

        $newStatus = TaskStatus::tryFrom($status);
        if (! $newStatus instanceof TaskStatus || $newStatus === $this->task->status) {
            return;
        }

        $oldStatus = $this->task->status;
        $wasCompleted = $this->task->status === TaskStatus::Completed;

        $this->task->update(['status' => $newStatus->value]);
        $this->dispatchTaskStatusChanged($oldStatus, $newStatus);

        if (! $wasCompleted && $newStatus === TaskStatus::Completed) {
            TaskCompleted::dispatch($this->task->fresh());
        }

        $this->dispatch('task-updated');
        $this->dispatch('task-moved');
    }

    public function toggleComplete(): void
    {
        if (! $this->canEdit) {
            return;
        }

        $wasCompleted = $this->task->status === TaskStatus::Completed;
        $oldStatus = $this->task->status;
        $newStatus = $wasCompleted ? TaskStatus::Todo : TaskStatus::Completed;
        $this->task->update(['status' => $newStatus->value]);
        $this->dispatchTaskStatusChanged($oldStatus, $newStatus);

        if (! $wasCompleted) {
            TaskCompleted::dispatch($this->task->fresh());
        }

        $this->dispatch('task-updated');
        $this->dispatch('task-moved');
    }

    public function startTask(): void
    {
        if (! $this->canEdit) {
            return;
        }

        if (! in_array($this->task->status, [TaskStatus::Backlog, TaskStatus::Todo], true)) {
            return;
        }

        $oldStatus = $this->task->status;
        $this->task->update(['status' => TaskStatus::InProgress->value]);
        $this->dispatchTaskStatusChanged($oldStatus, TaskStatus::InProgress);
        $this->dispatch('task-updated');
        $this->dispatch('task-moved');
    }

    protected function dispatchTaskStatusChanged(TaskStatus $fromStatus, TaskStatus $toStatus): void
    {
        $actor = auth()->user();
        if (! $actor instanceof \App\Models\User) {
            return;
        }

        $task = $this->task->fresh(['assignee']);
        if (! $task instanceof Task || $task->created_by === $actor->id) {
            return;
        }

        TaskStatusChanged::dispatch($task, $fromStatus, $toStatus, $actor);
    }

    public function render()
    {
        return view('components.task.⚡task-card.task-card');
    }
};
