<?php

declare(strict_types=1);

use App\Enums\TaskStatus;
use App\Models\Task;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public int $taskId;

    #[Validate('required|string|max:255')]
    public string $newSubtaskTitle = '';

    public function mount(int $taskId): void
    {
        $this->taskId = $taskId;
    }

    #[Computed]
    public function task(): ?Task
    {
        $task = Task::with(['assignee', 'creator', 'subtasks'])->find($this->taskId);

        return $task && $task->canBeEditedBy(auth()->user()) ? $task : null;
    }

    public function addSubtask(): void
    {
        $this->validateOnly('newSubtaskTitle');
        $parent = Task::find($this->taskId);
        if (! $parent || ! $parent->canBeEditedBy(auth()->user())) {
            return;
        }
        Task::create([
            'parent_id' => $parent->id,
            'title' => $this->newSubtaskTitle,
            'status' => TaskStatus::Todo,
            'priority' => $parent->priority,
            'assigned_to' => $parent->assigned_to,
            'created_by' => auth()->id(),
        ]);
        $this->newSubtaskTitle = '';
        unset($this->task);
    }

    public function toggleSubtaskComplete(int $subtaskId): void
    {
        $task = $this->task;
        if (! $task) {
            return;
        }
        $subtask = $task->subtasks->firstWhere('id', $subtaskId);
        if (! $subtask || ! $subtask->canBeEditedBy(auth()->user())) {
            return;
        }
        $newStatus = $subtask->status === TaskStatus::Completed ? TaskStatus::Todo : TaskStatus::Completed;
        $subtask->update(['status' => $newStatus]);
        unset($this->task);
    }

    public function close(): void
    {
        $this->dispatch('close-task-detail');
    }

    public function render()
    {
        return view('components.task.⚡task-detail-modal.task-detail-modal');
    }
}
