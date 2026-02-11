<?php

use App\Enums\Mood;
use App\Enums\StandupTaskStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Standup;
use App\Models\Task;
use App\Services\GamificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public $standup = null;

    public bool $isEditing = false;

    public $currentStep = 1;

    public $totalSteps = 3;

    // Step 1: Yesterday's tasks
    public array $completedTaskIds = [];

    public array $carriedOverTaskIds = [];

    // Step 2: Today's plan
    public array $plannedTaskIds = [];

    /** Task IDs user has clicked "Start" on (ongoing today). */
    public array $ongoingTaskIds = [];

    public array $blockedTaskIds = [];

    // Step 3: Wrap up
    #[Validate('nullable|string|max:1000')]
    public string $blockers = '';

    #[Validate('nullable|string|in:firing,steady,strong,struggling,blocked')]
    public ?string $mood = null;

    // Quick task creation
    #[Validate('required|string|max:255', as: 'task title')]
    public string $newTaskTitle = '';

    public string $taskSearch = '';

    public array $pointsEarned = [];

    public array $earnedBadges = [];

    public bool $showSuccessModal = false;

    public function mount(Standup|int|string|null $standup = null): void
    {
        $requestedId = $standup !== null && ! $standup instanceof Standup;
        $existingStandup = $standup instanceof Standup
            ? $standup
            : ($requestedId
                ? Auth::user()->standups()->find($standup)
                : null);

        if ($requestedId && $existingStandup === null) {
            abort(404);
        }

        $existingStandup ??= Auth::user()->todaysStandup();

        if ($existingStandup) {
            $this->standup = $existingStandup;
            $this->isEditing = true;
            $this->loadExistingStandup();
        }
    }

    protected function loadExistingStandup(): void
    {
        if (! $this->standup) {
            return;
        }

        $this->blockers = $this->standup->blockers ?? '';
        $this->mood = $this->standup->mood?->value;

        // Load tasks by status
        foreach ($this->standup->tasks as $task) {
            $pivotStatus = $task->pivot->status;

            match ($pivotStatus) {
                StandupTaskStatus::Completed->value, 'completed' => $this->completedTaskIds[] = $task->id,
                StandupTaskStatus::Planned->value, 'planned' => $this->plannedTaskIds[] = $task->id,
                StandupTaskStatus::Ongoing->value, 'ongoing' => $this->ongoingTaskIds[] = $task->id,
                StandupTaskStatus::CarriedOver->value, 'carried_over' => $this->carriedOverTaskIds[] = $task->id,
                StandupTaskStatus::Blocked->value, 'blocked' => $this->blockedTaskIds[] = $task->id,
                default => null,
            };
        }
    }

    #[Computed]
    public function yesterdayTasks()
    {
        $user = Auth::user();
        $yesterday = today()->subDay();

        // Get tasks from yesterday's standup that were planned
        $previousStandup = $user->standups()
            ->whereDate('date', $yesterday)
            ->first();

        $previousPlannedTaskIds = [];
        if ($previousStandup) {
            $previousPlannedTaskIds = DB::table('standup_task')
                ->where('standup_id', $previousStandup->id)
                ->whereIn('status', [
                    StandupTaskStatus::Planned->value,
                    StandupTaskStatus::Ongoing->value,
                ])
                ->pluck('task_id')
                ->toArray();
        }

        // Also include any tasks currently in progress assigned to user
        return Task::query()
            ->where('assigned_to', $user->id)
            ->where(function ($query) use ($previousPlannedTaskIds) {
                $query->whereIn('status', [TaskStatus::InProgress, TaskStatus::Review])
                    ->orWhereIn('id', $previousPlannedTaskIds);
            })
            ->whereNot('status', TaskStatus::Completed)
            ->get()
            ->sortBy(fn (Task $task) => match ($task->status) {
                TaskStatus::InProgress => 1,
                TaskStatus::Review => 2,
                TaskStatus::Todo => 3,
                TaskStatus::Backlog => 4,
                default => 99,
            });
    }

    #[Computed]
    public function backlogTasks()
    {
        $search = trim($this->taskSearch);
        $user = Auth::user();

        // Exclude tasks already selected (planned or started)
        $excludeIds = array_merge(
            $this->completedTaskIds,
            $this->plannedTaskIds,
            $this->ongoingTaskIds,
            $this->blockedTaskIds,
            $this->carriedOverTaskIds
        );

        return Task::query()
            ->where('assigned_to', $user->id)
            ->whereIn('status', [TaskStatus::Backlog, TaskStatus::Todo])
            ->whereNotIn('id', $excludeIds)
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->orderBy('priority')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    public function startTaskToOngoing(int $taskId): void
    {
        if (! in_array($taskId, $this->plannedTaskIds, true)) {
            return;
        }
        $this->plannedTaskIds = array_values(array_diff($this->plannedTaskIds, [$taskId]));
        $this->ongoingTaskIds = array_values(array_unique([...$this->ongoingTaskIds, $taskId]));
    }

    #[Computed]
    public function selectedOngoingTasks()
    {
        if (empty($this->ongoingTaskIds)) {
            return collect();
        }

        $tasks = Task::query()->whereIn('id', $this->ongoingTaskIds)->get();

        return collect($this->ongoingTaskIds)->map(fn (int $id) => $tasks->firstWhere('id', $id))->filter();
    }

    #[Computed]
    public function selectedPlannedTasks()
    {
        if (empty($this->plannedTaskIds)) {
            return collect();
        }

        $tasks = Task::query()->whereIn('id', $this->plannedTaskIds)->get();

        return collect($this->plannedTaskIds)->map(fn (int $id) => $tasks->firstWhere('id', $id))->filter();
    }

    #[Computed]
    public function summaryStats(): array
    {
        return [
            'completed' => count($this->completedTaskIds),
            'carried_over' => count($this->carriedOverTaskIds),
            'planned' => count($this->plannedTaskIds),
            'ongoing' => count($this->ongoingTaskIds),
            'blocked' => count($this->blockedTaskIds),
        ];
    }

    // Task toggle methods
    public function toggleCompleted(int $taskId): void
    {
        $this->toggleArrayItem($this->completedTaskIds, $taskId);

        // If marking as completed, remove from carried over
        if (in_array($taskId, $this->completedTaskIds)) {
            $this->carriedOverTaskIds = array_values(array_diff($this->carriedOverTaskIds, [$taskId]));
        }
    }

    public function toggleCarriedOver(int $taskId): void
    {
        $this->toggleArrayItem($this->carriedOverTaskIds, $taskId);

        // If carrying over, remove from completed
        if (in_array($taskId, $this->carriedOverTaskIds)) {
            $this->completedTaskIds = array_values(array_diff($this->completedTaskIds, [$taskId]));
            // Also add to planned for today
            if (! in_array($taskId, $this->plannedTaskIds)) {
                $this->plannedTaskIds[] = $taskId;
            }
        }
    }

    public function togglePlanned(int $taskId): void
    {
        $this->toggleArrayItem($this->plannedTaskIds, $taskId);

        // If removing from planned, also remove from blocked
        if (! in_array($taskId, $this->plannedTaskIds)) {
            $this->blockedTaskIds = array_values(array_diff($this->blockedTaskIds, [$taskId]));
        }
    }

    public function toggleBlocked(int $taskId): void
    {
        $this->toggleArrayItem($this->blockedTaskIds, $taskId);
    }

    public function sortPlannedTasks(mixed $taskId, mixed $position): void
    {
        $taskId = (int) $taskId;
        $position = (int) $position;

        if (! in_array($taskId, $this->plannedTaskIds, true)) {
            return;
        }

        $this->plannedTaskIds = $this->moveIdToPosition($this->plannedTaskIds, $taskId, $position);
    }

    protected function toggleArrayItem(array &$array, int $item): void
    {
        if (in_array($item, $array)) {
            $array = array_values(array_diff($array, [$item]));
        } else {
            $array[] = $item;
        }
    }

    protected function moveIdToPosition(array $ids, int $taskId, int $position): array
    {
        $ids = array_values(array_filter($ids, fn (int $id) => $id !== $taskId));
        $position = max(0, min($position, count($ids)));

        array_splice($ids, $position, 0, [$taskId]);

        return $ids;
    }

    public function createQuickTask(): void
    {
        $this->newTaskTitle = trim($this->newTaskTitle);
        $this->validateOnly('newTaskTitle');

        $task = Task::create([
            'title' => $this->newTaskTitle,
            'assigned_to' => Auth::id(),
            'created_by' => Auth::id(),
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Medium,
        ]);

        $this->plannedTaskIds[] = $task->id;
        $this->newTaskTitle = '';

        // Clear computed cache
        unset($this->backlogTasks);

        $this->dispatch('task-created', taskId: $task->id);
    }

    public function removeFromPlanned(int $taskId): void
    {
        $this->plannedTaskIds = array_values(array_diff($this->plannedTaskIds, [$taskId]));
        $this->blockedTaskIds = array_values(array_diff($this->blockedTaskIds, [$taskId]));
    }

    // Navigation
    public function nextStep(): void
    {
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= $this->totalSteps) {
            $this->currentStep = $step;
        }
    }

    public function submitStandup(): void
    {
        $this->validate([
            'blockers' => 'nullable|string|max:1000',
            'mood' => 'nullable|string|in:firing,steady,strong,struggling,blocked',
        ]);

        $user = Auth::user();
        $isNew = ! $this->isEditing;

        DB::transaction(function () use ($user) {
            // Create or update standup
            if ($this->isEditing && $this->standup) {
                $this->standup->update([
                    'blockers' => $this->blockers ?: null,
                    'mood' => $this->mood,
                ]);

                // Clear existing task associations
                DB::table('standup_task')
                    ->where('standup_id', $this->standup->id)
                    ->delete();

            } else {
                $this->standup = Standup::create([
                    'user_id' => $user->id,
                    'date' => today(),
                    'blockers' => $this->blockers ?: null,
                    'mood' => $this->mood,
                ]);
            }

            // Save completed tasks
            foreach ($this->completedTaskIds as $taskId) {
                $this->attachTaskToStandup($taskId, StandupTaskStatus::Completed);
                Task::find($taskId)?->update(['status' => TaskStatus::Completed]);
            }

            // Save carried over tasks (mark as worked on in standup)
            foreach ($this->carriedOverTaskIds as $taskId) {
                if (! in_array($taskId, $this->completedTaskIds)) {
                    $this->attachTaskToStandup($taskId, StandupTaskStatus::CarriedOver);
                }
            }

            // Save ongoing (started) tasks
            foreach ($this->ongoingTaskIds as $taskId) {
                if (in_array($taskId, $this->completedTaskIds) || in_array($taskId, $this->carriedOverTaskIds)) {
                    continue;
                }
                $this->attachTaskToStandup($taskId, StandupTaskStatus::Ongoing);
                Task::find($taskId)?->update(['status' => TaskStatus::InProgress->value]);
            }

            // Save planned tasks (not yet started)
            foreach ($this->plannedTaskIds as $taskId) {
                if (in_array($taskId, $this->completedTaskIds) || in_array($taskId, $this->carriedOverTaskIds)
                    || in_array($taskId, $this->ongoingTaskIds, true)) {
                    continue;
                }

                $status = in_array($taskId, $this->blockedTaskIds)
                    ? StandupTaskStatus::Blocked
                    : StandupTaskStatus::Planned;

                $this->attachTaskToStandup($taskId, $status);

                $newTaskStatus = in_array($taskId, $this->blockedTaskIds)
                    ? TaskStatus::Blocked
                    : TaskStatus::InProgress;

                Task::find($taskId)?->update(['status' => $newTaskStatus->value]);
            }

        });

        if ($isNew) {
            $result = app(GamificationService::class)->processCheckin($user->fresh(), $this->standup);
            $this->pointsEarned = $result['points'];
            $this->earnedBadges = $result['badges'];
            $this->showSuccessModal = true;
        } else {
            session()->flash('status', 'Standup updated successfully!');
            $this->redirectRoute('standups', navigate: true);
        }
    }

    protected function attachTaskToStandup(int $taskId, StandupTaskStatus $status): void
    {
        DB::table('standup_task')->insert([
            'standup_id' => $this->standup->id,
            'task_id' => $taskId,
            'status' => $status->value,
            'notes' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function closeSuccessModal(): void
    {
        $this->redirectRoute('standups', navigate: true);
    }

    public function render()
    {
        return view('components.standup.⚡standup-form.standup-form', [
            'moods' => Mood::cases(),
        ]);
    }
};
