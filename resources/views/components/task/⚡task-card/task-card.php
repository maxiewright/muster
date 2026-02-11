<?php

use App\Enums\TaskStatus;
use App\Events\TaskCompleted;
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
        $wasCompleted = $this->task->status === TaskStatus::Completed;

        $this->task->update(['status' => $status]);

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
        $newStatus = $wasCompleted ? TaskStatus::Todo : TaskStatus::Completed;
        $this->task->update(['status' => $newStatus->value]);

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

        $this->task->update(['status' => TaskStatus::InProgress->value]);
        $this->dispatch('task-updated');
        $this->dispatch('task-moved');
    }

    public function render()
    {
        return view('components.task.⚡task-card.task-card');
    }
};
