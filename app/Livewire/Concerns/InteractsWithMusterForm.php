<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Enums\Mood;
use App\Enums\MusterTaskStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Livewire\Forms\DailyMusterForm;
use App\Models\Muster;
use App\Models\Task;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

trait InteractsWithMusterForm
{
    public ?Muster $muster = null;

    public bool $isEditing = false;

    public int $currentStep = 1;

    public int $totalSteps = 3;

    public array $completedTaskIds = [];

    public array $carriedOverTaskIds = [];

    public array $plannedTaskIds = [];

    /** @var array<int> */
    public array $ongoingTaskIds = [];

    public array $blockedTaskIds = [];

    public DailyMusterForm $form;

    public string $taskSearch = '';

    public array $pointsEarned = [];

    public array $earnedBadges = [];

    public bool $showSuccessModal = false;

    public function mount(Muster|int|string|null $muster = null): void
    {
        $requestedMuster = $muster;
        $requestedId = $requestedMuster !== null && ! $requestedMuster instanceof Muster;
        $existingMuster = $requestedMuster instanceof Muster
            ? $requestedMuster
            : ($requestedId
                ? Auth::user()->musters()->find($requestedMuster)
                : null);

        if ($requestedId && $existingMuster === null) {
            abort(404);
        }

        $existingMuster ??= Auth::user()->todaysMuster();

        if ($existingMuster) {
            $this->muster = $existingMuster;
            $this->isEditing = true;
            $this->loadExistingMuster();
        }
    }

    protected function loadExistingMuster(): void
    {
        if (! $this->muster) {
            return;
        }

        $this->form->blockers = $this->muster->blockers ?? '';
        $this->form->mood = $this->muster->mood?->value;

        foreach ($this->muster->tasks as $task) {
            $pivotStatus = $task->pivot->status;

            match ($pivotStatus) {
                MusterTaskStatus::Completed->value, 'completed' => $this->completedTaskIds[] = $task->id,
                MusterTaskStatus::Planned->value, 'planned' => $this->plannedTaskIds[] = $task->id,
                MusterTaskStatus::Ongoing->value, 'ongoing' => $this->ongoingTaskIds[] = $task->id,
                MusterTaskStatus::CarriedOver->value, 'carried_over' => $this->carriedOverTaskIds[] = $task->id,
                MusterTaskStatus::Blocked->value, 'blocked' => $this->blockedTaskIds[] = $task->id,
                default => null,
            };
        }
    }

    #[Computed]
    public function yesterdayTasks()
    {
        $user = Auth::user();
        $yesterday = today()->subDay();
        $activeUnitId = $user?->activeUnitId();

        $previousMuster = $user?->musterForDate($yesterday, $activeUnitId);

        $previousPlannedTaskIds = [];
        if ($previousMuster) {
            $previousPlannedTaskIds = DB::table('muster_task')
                ->where('muster_id', $previousMuster->id)
                ->whereIn('status', [
                    MusterTaskStatus::Planned->value,
                    MusterTaskStatus::Ongoing->value,
                ])
                ->pluck('task_id')
                ->toArray();
        }

        return Task::query()
            ->inUnit($activeUnitId)
            ->where('assigned_to', $user->id)
            ->where(function ($query) use ($previousPlannedTaskIds): void {
                $query->whereIn('status', [TaskStatus::InProgress, TaskStatus::Review])
                    ->orWhereIn('id', $previousPlannedTaskIds);
            })
            ->whereNot('status', TaskStatus::Completed)
            ->get()
            ->sortBy(fn (Task $task): int => match ($task->status) {
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
        $activeUnitId = $user?->activeUnitId();

        $excludeIds = array_merge(
            $this->completedTaskIds,
            $this->plannedTaskIds,
            $this->ongoingTaskIds,
            $this->blockedTaskIds,
            $this->carriedOverTaskIds
        );

        return Task::query()
            ->inUnit($activeUnitId)
            ->where('assigned_to', $user->id)
            ->whereIn('status', [TaskStatus::Backlog, TaskStatus::Todo])
            ->whereNotIn('id', $excludeIds)
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->orderBy('priority')->latest()
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
        if ($this->ongoingTaskIds === []) {
            return collect();
        }

        $tasks = Task::query()->whereIn('id', $this->ongoingTaskIds)->get();

        return collect($this->ongoingTaskIds)->map(fn (int $id) => $tasks->firstWhere('id', $id))->filter();
    }

    #[Computed]
    public function selectedPlannedTasks()
    {
        if ($this->plannedTaskIds === []) {
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

    public function toggleCompleted(int $taskId): void
    {
        $this->toggleArrayItem($this->completedTaskIds, $taskId);

        if (in_array($taskId, $this->completedTaskIds, true)) {
            $this->carriedOverTaskIds = array_values(array_diff($this->carriedOverTaskIds, [$taskId]));
        }
    }

    public function toggleCarriedOver(int $taskId): void
    {
        $this->toggleArrayItem($this->carriedOverTaskIds, $taskId);

        if (in_array($taskId, $this->carriedOverTaskIds, true)) {
            $this->completedTaskIds = array_values(array_diff($this->completedTaskIds, [$taskId]));

            if (! in_array($taskId, $this->plannedTaskIds, true)) {
                $this->plannedTaskIds[] = $taskId;
            }
        }
    }

    public function togglePlanned(int $taskId): void
    {
        $this->toggleArrayItem($this->plannedTaskIds, $taskId);

        if (! in_array($taskId, $this->plannedTaskIds, true)) {
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
        if (in_array($item, $array, true)) {
            $array = array_values(array_diff($array, [$item]));
        } else {
            $array[] = $item;
        }
    }

    protected function moveIdToPosition(array $ids, int $taskId, int $position): array
    {
        $ids = array_values(array_filter($ids, fn (int $id): bool => $id !== $taskId));
        $position = max(0, min($position, count($ids)));

        array_splice($ids, $position, 0, [$taskId]);

        return $ids;
    }

    public function createQuickTask(): void
    {
        $this->form->newTaskTitle = trim($this->form->newTaskTitle);
        $this->form->validateOnly('newTaskTitle');

        $task = Task::create([
            'organization_id' => Auth::user()?->activeUnit()?->organization_id,
            'unit_id' => Auth::user()?->activeUnitId(),
            'title' => $this->form->newTaskTitle,
            'assigned_to' => Auth::id(),
            'created_by' => Auth::id(),
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Medium,
        ]);

        $this->plannedTaskIds[] = $task->id;
        $this->form->newTaskTitle = '';

        unset($this->backlogTasks);

        $this->dispatch('task-created', taskId: $task->id);
    }

    public function removeFromPlanned(int $taskId): void
    {
        $this->plannedTaskIds = array_values(array_diff($this->plannedTaskIds, [$taskId]));
        $this->blockedTaskIds = array_values(array_diff($this->blockedTaskIds, [$taskId]));
    }

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

    public function submitMuster(): void
    {
        $this->form->validateOnly('blockers');
        $this->form->validateOnly('mood');

        $user = Auth::user();
        $isNew = ! $this->isEditing;

        DB::transaction(function () use ($user): void {
            if ($this->isEditing && $this->muster) {
                $this->muster->update([
                    'blockers' => $this->form->blockers ?: null,
                    'mood' => $this->form->mood,
                ]);

                DB::table('muster_task')
                    ->where('muster_id', $this->muster->id)
                    ->delete();
            } else {
                $this->muster = Muster::create([
                    'organization_id' => $user->activeUnit()?->organization_id,
                    'unit_id' => $user->activeUnitId(),
                    'user_id' => $user->id,
                    'date' => today(),
                    'blockers' => $this->form->blockers ?: null,
                    'mood' => $this->form->mood,
                ]);
            }

            foreach ($this->completedTaskIds as $taskId) {
                $task = $this->resolveMusterTask($user, $taskId);
                $this->attachTaskToMuster($taskId, MusterTaskStatus::Completed);
                $task->update(['status' => TaskStatus::Completed]);
            }

            foreach ($this->carriedOverTaskIds as $taskId) {
                if (! in_array($taskId, $this->completedTaskIds, true)) {
                    $this->attachTaskToMuster($taskId, MusterTaskStatus::CarriedOver);
                }
            }

            foreach ($this->ongoingTaskIds as $taskId) {
                if (in_array($taskId, $this->completedTaskIds, true)) {
                    continue;
                }

                if (in_array($taskId, $this->carriedOverTaskIds, true)) {
                    continue;
                }

                $task = $this->resolveMusterTask($user, $taskId);
                $this->attachTaskToMuster($taskId, MusterTaskStatus::Ongoing);
                $task->update(['status' => TaskStatus::InProgress->value]);
            }

            foreach ($this->plannedTaskIds as $taskId) {
                if (in_array($taskId, $this->completedTaskIds, true)) {
                    continue;
                }

                if (in_array($taskId, $this->carriedOverTaskIds, true)) {
                    continue;
                }

                if (in_array($taskId, $this->ongoingTaskIds, true)) {
                    continue;
                }

                $status = in_array($taskId, $this->blockedTaskIds, true)
                    ? MusterTaskStatus::Blocked
                    : MusterTaskStatus::Planned;

                $this->attachTaskToMuster($taskId, $status);

                $newTaskStatus = in_array($taskId, $this->blockedTaskIds, true)
                    ? TaskStatus::Blocked
                    : TaskStatus::InProgress;

                $task = $this->resolveMusterTask($user, $taskId);
                $task->update(['status' => $newTaskStatus->value]);
            }
        });

        if ($isNew) {
            $result = resolve(GamificationService::class)->processCheckin($user->fresh(), $this->muster);
            $this->pointsEarned = $result['points'];
            $this->earnedBadges = $result['badges'];
            $this->showSuccessModal = true;
        } else {
            session()->flash('status', 'Muster updated successfully!');
            $this->redirectRoute('musters', navigate: true);
        }
    }

    protected function attachTaskToMuster(int $taskId, MusterTaskStatus $status): void
    {
        DB::table('muster_task')->insert([
            'muster_id' => $this->muster?->id,
            'task_id' => $taskId,
            'status' => $status->value,
            'notes' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function resolveMusterTask(User $user, int $taskId): Task
    {
        $task = Task::query()
            ->inUnit($user->activeUnitId())
            ->whereKey($taskId)
            ->where('assigned_to', $user->id)
            ->first();

        abort_if($task === null, 403);

        return $task;
    }

    public function closeSuccessModal(): void
    {
        $this->redirectRoute('musters', navigate: true);
    }

    public function render(): Factory|View
    {
        return view('components.muster.⚡muster-form.muster-form', [
            'moods' => Mood::cases(),
        ]);
    }
}
