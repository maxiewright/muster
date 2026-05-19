<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Events\TaskCompleted;
use App\Events\TaskStatusChanged;
use App\Models\Task;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function onTaskCompleted(array $payload): void
    {
        unset($this->tasks);
    }

    #[Computed]
    public function tasks()
    {
        $activeUnitId = auth()->user()?->activeUnitId();
        $query = Task::with(['assignee', 'creator', 'subtasks'])
            ->inUnit($activeUnitId)
            ->withCount([
                'subtasks',
                'subtasks as subtasks_completed_count' => function ($query): void {
                    $query->where('status', TaskStatus::Completed);
                },
                'musters',
            ])
            ->rootTasks()
            ->latest('updated_at');

        // Apply filters
        if ($this->filterAssignee !== '' && $this->filterAssignee !== '0') {
            if ($this->filterAssignee === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $this->filterAssignee);
            }
        }

        if ($this->filterPriority !== '' && $this->filterPriority !== '0') {
            $query->where('priority', $this->filterPriority);
        }

        if ($this->filterStatus !== '' && $this->filterStatus !== '0') {
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
        $activeUnitId = auth()->user()?->activeUnitId();

        return Task::with(['assignee', 'creator', 'subtasks'])
            ->inUnit($activeUnitId)
            ->withCount(['subtasks', 'musters'])
            /** @var User $user */
            ->where('assigned_to', auth()->id())
            ->whereNot('status', TaskStatus::Completed)
            ->orderBy('priority')
            ->oldest('due_date')
            ->get();
    }

    #[Computed]
    public function teamMembers()
    {
        $activeUnit = auth()->user()?->activeUnit();

        if ($activeUnit instanceof Unit) {
            return $activeUnit->users()->orderBy('name')->get();
        }

        return User::orderBy('name')->get();
    }

    #[Computed]
    public function stats(): array
    {
        $activeUnitId = auth()->user()?->activeUnitId();
        $allTasks = Task::query()->inUnit($activeUnitId);

        return [
            'total' => $allTasks->count(),
            'completed' => Task::query()->inUnit($activeUnitId)->where('status', TaskStatus::Completed)->count(),
            'in_progress' => Task::query()->inUnit($activeUnitId)->where('status', TaskStatus::InProgress)->count(),
            'overdue' => Task::query()->inUnit($activeUnitId)->overdue()->count(),
            'unassigned' => Task::query()->inUnit($activeUnitId)->unassigned()->whereNot('status', TaskStatus::Completed)->count(),
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
        $this->sortTaskToStatus($taskId, TaskStatus::Todo);
    }

    public function sortTaskToInProgress(mixed $taskId, mixed $position): void
    {
        $this->sortTaskToStatus($taskId, TaskStatus::InProgress);
    }

    public function sortTaskToReview(mixed $taskId, mixed $position): void
    {
        $this->sortTaskToStatus($taskId, TaskStatus::Review);
    }

    public function sortTaskToCompleted(mixed $taskId, mixed $position): void
    {
        $this->sortTaskToStatus($taskId, TaskStatus::Completed);
    }

    private function sortTaskToStatus(mixed $taskId, TaskStatus $targetStatus): void
    {
        DB::transaction(function () use ($taskId, $targetStatus): void {
            $task = Task::query()->lockForUpdate()->find($taskId);

            if (! $task || $task->status === $targetStatus) {
                return;
            }

            if (! $task->canBeEditedBy(auth()->user())) {
                return;
            }

            $wasCompleted = $task->status === TaskStatus::Completed;
            $oldStatus = $task->status;
            $task->update(['status' => $targetStatus]);
            $actor = auth()->user();
            if ($actor instanceof User && $task->created_by !== $actor->id) {
                event(new TaskStatusChanged($task->fresh(['assignee']), $oldStatus, $targetStatus, $actor));
            }

            if (! $wasCompleted && $targetStatus === TaskStatus::Completed) {
                event(new TaskCompleted($task->fresh()));
            }
        });

        unset($this->tasks);
    }

    public function render(): Factory|View
    {
        return view('components.task.⚡task-board.task-board');
    }
};
