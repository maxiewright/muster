<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Events\TaskAssigned;
use App\Events\TaskCreated;
use App\Models\Task;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public ?int $taskId = null;

    public ?string $presetStatus = null;

    public ?int $presetAssignee = null;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:2000')]
    public string $description = '';

    #[Validate('required|string')]
    public string $status = 'todo';

    #[Validate('required|string')]
    public string $priority = 'medium';

    #[Validate('nullable|exists:users,id')]
    public ?int $assigned_to = null;

    #[Validate('nullable|date')]
    public ?string $due_date = null;

    #[Validate('nullable|string|max:2000')]
    public string $notes = '';

    public function mount(?int $taskId = null, ?string $presetStatus = null, ?int $presetAssignee = null): void
    {
        $this->taskId = $taskId;
        $this->presetStatus = $presetStatus;
        $this->presetAssignee = $presetAssignee;

        if ($taskId) {
            $task = Task::findOrFail($taskId);
            $this->title = $task->title;
            $this->description = $task->description ?? '';
            $this->status = $task->status->value;
            $this->priority = $task->priority->value;
            $this->assigned_to = $task->assigned_to;
            $this->due_date = $task->due_date?->format('Y-m-d');
            $this->notes = $task->notes ?? '';
        } else {
            // Apply presets for new tasks
            if ($presetStatus) {
                $this->status = $presetStatus;
            }
            if ($presetAssignee) {
                $this->assigned_to = $presetAssignee;
            }
        }
    }

    #[Computed]
    public function task(): ?Task
    {
        return $this->taskId ? Task::find($this->taskId) : null;
    }

    #[Computed]
    public function teamMembers()
    {
        return User::orderBy('name')->get();
    }

    #[Computed]
    public function statuses(): array
    {
        return TaskStatus::cases();
    }

    #[Computed]
    public function priorities(): array
    {
        return TaskPriority::cases();
    }

    #[Computed]
    public function canAssign(): bool
    {
        return auth()->user()->canAssignTasks();
    }

    #[Computed]
    public function canDelete(): bool
    {
        if (! $this->task) {
            return false;
        }

        $user = auth()->user();

        return $user->isLead() || $this->task->created_by === $user->id;
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();

        if ($this->taskId) {
            $task = Task::findOrFail($this->taskId);
            $this->authorize('update', $task);
        } else {
            $this->authorize('create', Task::class);
        }

        // Check if user can assign to others
        if ($this->assigned_to && $this->assigned_to !== $user->id && ! $user->canAssignTasks()) {
            $this->addError('assigned_to', 'You do not have permission to assign tasks to others.');

            return;
        }

        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'status' => $this->status,
            'priority' => $this->priority,
            'assigned_to' => $this->assigned_to,
            'due_date' => $this->due_date ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->taskId) {
            $task = Task::findOrFail($this->taskId);
            $task->update($data);
        } else {
            $data['created_by'] = $user->id;
            $task = Task::create($data);
            TaskCreated::dispatch($task);
            if ($task->assigned_to !== null && $task->assigned_to !== $task->created_by) {
                TaskAssigned::dispatch($task->fresh(['assignee', 'creator']));
            }
        }

        $this->dispatch('task-saved');
    }

    public function delete(): void
    {
        if (! $this->task) {
            return;
        }

        $this->authorize('delete', $this->task);

        $this->task->delete();
        $this->dispatch('task-deleted');
    }

    public function render()
    {
        return view('components.task.⚡create-task-modal.create-task-modal');
    }
};
