<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Events\TaskCompleted;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url]
    public string $view = 'board'; // board, list, my-tasks

    #[Url]
    public string $filterAssignee = '';

    #[Url]
    public string $filterPriority = '';

    #[Url]
    public string $filterStatus = '';

    public bool $showCreateModal = false;

    public bool $showTaskDetailModal = false;

    public ?int $selectedTaskId = null;

    public ?int $editingTaskId = null;

    public ?string $presetStatus = null;

    public ?int $presetAssignee = null;

    #[On('open-create-task-modal')]
    public function openCreateModal(?string $status = null, ?int $assignee = null): void
    {
        $this->presetStatus = $status;
        $this->presetAssignee = $assignee;
        $this->editingTaskId = null;
        $this->showCreateModal = true;
    }

    public function editTask(int $taskId): void
    {
        $this->editingTaskId = $taskId;
        $this->presetStatus = null;
        $this->presetAssignee = null;
        $this->showCreateModal = true;
    }

    #[On('close-modal')]
    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->editingTaskId = null;
        $this->presetStatus = null;
        $this->presetAssignee = null;
    }

    #[On('open-task-detail')]
    public function openTaskDetail(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
        $this->showTaskDetailModal = true;
    }

    #[On('close-task-detail')]
    public function closeTaskDetail(): void
    {
        $this->showTaskDetailModal = false;
        $this->selectedTaskId = null;
        unset($this->tasks);
    }

    #[On('task-saved')]
    public function handleTaskSaved(): void
    {
        $this->closeModal();
        unset($this->tasks);
    }

    #[On('task-deleted')]
    public function handleTaskDeleted(): void
    {
        $this->closeModal();
        unset($this->tasks);
    }

    #[On('task-moved')]
    public function handleTaskMoved(): void
    {
        unset($this->tasks);
    }

    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        return [
            'echo-private:team,TaskCreated' => 'onTaskCreated',
            'echo-private:team,TaskCompleted' => 'onTaskCompleted',
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function onTaskCreated(array $payload): void
    {
        unset($this->tasks);
        $task = $payload['task'] ?? [];
        $title = is_string($task['title'] ?? null) ? $task['title'] : 'A task';
        $creator = is_string($task['creator_name'] ?? null) ? $task['creator_name'] : 'Someone';
        $this->dispatch('toast-show', duration: 5000, slots: ['text' => "{$creator} added: {$title}", 'heading' => 'New task'], dataset: ['variant' => 'success']);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function onTaskCompleted(array $payload): void
    {
        unset($this->tasks);
        $task = $payload['task'] ?? [];
        $title = is_string($task['title'] ?? null) ? $task['title'] : 'A task';
        $assignee = is_string($task['assignee_name'] ?? null) ? $task['assignee_name'] : 'Someone';
        $this->dispatch('toast-show', duration: 5000, slots: ['text' => "{$assignee} completed: {$title}", 'heading' => 'Task completed'], dataset: ['variant' => 'success']);
    }

    #[Computed]
    public function tasks()
    {
        $query = Task::with(['assignee', 'creator', 'subtasks'])
            ->withCount([
                'subtasks',
                'subtasks as subtasks_completed_count' => function ($query) {
                    $query->where('status', TaskStatus::Completed);
                },
                'standups',
            ])
            ->rootTasks()
            ->latest('updated_at');

        // Apply filters
        if ($this->filterAssignee) {
            if ($this->filterAssignee === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $this->filterAssignee);
            }
        }

        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return $query->get();
    }

    #[Computed]
    public function tasksByStatus()
    {
        return $this->tasks->groupBy(fn ($task) => $task->status->value);
    }

    #[Computed]
    public function myTasks()
    {
        return Task::with(['assignee', 'creator', 'subtasks'])
            ->withCount(['subtasks', 'standups'])
            /** @var \App\Models\User $user */
            ->where('assigned_to', auth()->id())
            ->whereNot('status', TaskStatus::Completed)
            ->orderBy('priority')
            ->orderBy('due_date')
            ->get();
    }

    #[Computed]
    public function teamMembers()
    {
        return User::orderBy('name')->get();
    }

    #[Computed]
    public function stats(): array
    {
        $allTasks = Task::query();

        return [
            'total' => $allTasks->count(),
            'completed' => Task::where('status', TaskStatus::Completed)->count(),
            'in_progress' => Task::where('status', TaskStatus::InProgress)->count(),
            'overdue' => Task::overdue()->count(),
            'unassigned' => Task::unassigned()->whereNot('status', TaskStatus::Completed)->count(),
        ];
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
    public function boardColumns(): array
    {
        return [
            TaskStatus::Todo,
            TaskStatus::InProgress,
            TaskStatus::Review,
            TaskStatus::Completed,
        ];
    }

    public function sortTaskToTodo(mixed $taskId, mixed $position): void
    {
        $this->sortTaskToStatus($taskId, $position, TaskStatus::Todo);
    }

    public function sortTaskToInProgress(mixed $taskId, mixed $position): void
    {
        $this->sortTaskToStatus($taskId, $position, TaskStatus::InProgress);
    }

    public function sortTaskToReview(mixed $taskId, mixed $position): void
    {
        $this->sortTaskToStatus($taskId, $position, TaskStatus::Review);
    }

    public function sortTaskToCompleted(mixed $taskId, mixed $position): void
    {
        $this->sortTaskToStatus($taskId, $position, TaskStatus::Completed);
    }

    private function sortTaskToStatus(mixed $taskId, mixed $position, TaskStatus $targetStatus): void
    {
        DB::transaction(function () use ($taskId, $targetStatus): void {
            $task = Task::query()->lockForUpdate()->find($taskId);

            if (! $task || $task->status === $targetStatus) {
                return;
            }

            /** @var \App\Models\User $user */
            if (! $task->canBeEditedBy(auth()->user())) {
                return;
            }

            $wasCompleted = $task->status === TaskStatus::Completed;
            $task->update(['status' => $targetStatus]);

            if (! $wasCompleted && $targetStatus === TaskStatus::Completed) {
                TaskCompleted::dispatch($task->fresh());
            }
        });

        unset($this->tasks);
    }

    public function render()
    {
        return view('components.task.⚡task-board.task-board');
    }
};
