<?php

declare(strict_types=1);

use App\Enums\MusterTaskStatus;
use App\Enums\TaskStatus;
use App\Events\TaskStatusChanged;
use App\Models\Muster;
use App\Models\MusterTask;
use App\Models\Task;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public int $musterId;

    public function mount(int $musterId): void
    {
        $this->musterId = $musterId;
        $muster = Muster::query()->findOrFail($this->musterId);

        if ($muster->user_id !== auth()->id() || ! $muster->isInUnit(auth()->user()?->activeUnitId())) {
            abort(403);
        }
    }

    #[Computed]
    public function muster(): Muster
    {
        $muster = Muster::query()
            ->with(['musterTasks.task'])
            ->findOrFail($this->musterId);

        if ($muster->user_id !== auth()->id() || ! $muster->isInUnit(auth()->user()?->activeUnitId())) {
            abort(403);
        }

        return $muster;
    }

    #[Computed]
    public function completedTasks(): Collection
    {
        return $this->muster->musterTasks->where('status', MusterTaskStatus::Completed);
    }

    #[Computed]
    public function plannedTasks(): Collection
    {
        return $this->muster->musterTasks->where('status', MusterTaskStatus::Planned);
    }

    #[Computed]
    public function ongoingTasks(): Collection
    {
        return $this->muster->musterTasks->where('status', MusterTaskStatus::Ongoing);
    }

    #[Computed]
    public function blockedTasks(): Collection
    {
        return $this->muster->musterTasks->where('status', MusterTaskStatus::Blocked);
    }

    public function markComplete(int $taskId): void
    {
        $this->updateMusterTaskStatus($taskId, MusterTaskStatus::Completed, TaskStatus::Completed);
    }

    public function uncomplete(int $taskId): void
    {
        $this->updateMusterTaskStatus($taskId, MusterTaskStatus::Ongoing, TaskStatus::InProgress);
    }

    public function startTask(int $taskId): void
    {
        $this->updateMusterTaskStatus($taskId, MusterTaskStatus::Ongoing, TaskStatus::InProgress);
    }

    public function toggleBlocked(int $taskId): void
    {
        $musterTask = MusterTask::query()
            ->where('muster_id', $this->musterId)
            ->where('task_id', $taskId)
            ->firstOrFail();

        if ($musterTask->status === MusterTaskStatus::Blocked) {
            $this->updateMusterTaskStatus($taskId, MusterTaskStatus::Planned, TaskStatus::InProgress);
        } else {
            $this->updateMusterTaskStatus($taskId, MusterTaskStatus::Blocked, TaskStatus::Blocked);
        }
    }

    protected function updateMusterTaskStatus(int $taskId, MusterTaskStatus $musterStatus, TaskStatus $taskStatus): void
    {
        $musterTask = MusterTask::query()
            ->where('muster_id', $this->musterId)
            ->where('task_id', $taskId)
            ->firstOrFail();

        if ($musterTask->muster->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $musterTask->muster->isInUnit(auth()->user()?->activeUnitId())) {
            abort(403);
        }

        $musterTask->update(['status' => $musterStatus]);

        $task = Task::query()->findOrFail($taskId);
        $oldStatus = $task->status;

        if ($oldStatus !== $taskStatus) {
            $task->update(['status' => $taskStatus]);

            $actor = auth()->user();

            if ($actor instanceof \App\Models\User && $task->created_by !== $actor->id) {
                event(new TaskStatusChanged($task->fresh(['assignee']), $oldStatus, $taskStatus, $actor));
            }
        }

        unset(
            $this->muster,
            $this->completedTasks,
            $this->plannedTasks,
            $this->ongoingTasks,
            $this->blockedTasks,
        );
    }
};
?>

<div class="space-y-2 text-sm">
    @php
        $completed = $this->completedTasks;
        $planned = $this->plannedTasks;
        $ongoing = $this->ongoingTasks;
        $blocked = $this->blockedTasks;
    @endphp

    @if($completed->isNotEmpty())
        <div>
            <flux:text variant="subtle" class="mb-1 block">Completed</flux:text>
            <ul class="space-y-1">
                @foreach($completed as $musterTask)
                    @if($musterTask->task)
                        <li wire:key="muster-completed-{{ $musterTask->id }}" class="flex items-center gap-2 group">
                            <flux:checkbox :checked="true"
                                           wire:click="uncomplete({{ $musterTask->task_id }})"
                                           class="!cursor-pointer"
                                           aria-label="Mark {{ $musterTask->task->title }} as not complete" />
                            <span class="flex-1 line-through opacity-70">{{ $musterTask->task->title }}</span>
                            <flux:button variant="ghost" size="xs" wire:click="uncomplete({{ $musterTask->task_id }})" wire:loading.attr="disabled">
                                Undo
                            </flux:button>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    @if($ongoing->isNotEmpty())
        <div>
            <flux:text variant="subtle" class="mb-1 block">Ongoing</flux:text>
            <ul class="space-y-1">
                @foreach($ongoing as $musterTask)
                    @if($musterTask->task)
                        <li wire:key="muster-ongoing-{{ $musterTask->id }}" class="flex items-center gap-2 group">
                            <flux:checkbox :checked="false"
                                           wire:click="markComplete({{ $musterTask->task_id }})"
                                           class="!cursor-pointer"
                                           aria-label="Mark {{ $musterTask->task->title }} complete" />
                            <span class="flex-1">{{ $musterTask->task->title }}</span>
                            <flux:button variant="ghost" size="xs" wire:click="toggleBlocked({{ $musterTask->task_id }})" wire:loading.attr="disabled" title="Mark as blocked">
                                🚧
                            </flux:button>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    @if($planned->isNotEmpty())
        <div>
            <flux:text variant="subtle" class="mb-1 block">Planned</flux:text>
            <ul class="space-y-1">
                @foreach($planned as $musterTask)
                    @if($musterTask->task)
                        <li wire:key="muster-planned-{{ $musterTask->id }}" class="flex items-center gap-2 group">
                            <span class="w-4 flex-shrink-0" aria-hidden="true"></span>
                            <span class="flex-1">{{ $musterTask->task->title }}</span>
                            <div class="flex items-center gap-1">
                                <flux:button variant="ghost" size="xs" wire:click="startTask({{ $musterTask->task_id }})" wire:loading.attr="disabled" title="Start">
                                    Start
                                </flux:button>
                                <flux:button variant="ghost" size="xs" wire:click="toggleBlocked({{ $musterTask->task_id }})" wire:loading.attr="disabled" title="Mark as blocked">
                                    🚧
                                </flux:button>
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    @if($blocked->isNotEmpty())
        <div class="flex items-start gap-1">
            <span class="text-red-500" aria-hidden="true">⚠️</span>
            <div class="flex-1 min-w-0">
                <flux:text variant="subtle" class="mb-1 block !text-red-600 dark:!text-red-400">Blocked</flux:text>
                <ul class="space-y-1">
                    @foreach($blocked as $musterTask)
                        @if($musterTask->task)
                            <li wire:key="muster-blocked-{{ $musterTask->id }}" class="flex items-center gap-2 group">
                                <span class="w-4 flex-shrink-0" aria-hidden="true"></span>
                                <span class="flex-1">{{ $musterTask->task->title }}</span>
                                <flux:button variant="ghost" size="xs" wire:click="toggleBlocked({{ $musterTask->task_id }})" wire:loading.attr="disabled" title="Remove blocker">
                                    Unblock
                                </flux:button>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>
