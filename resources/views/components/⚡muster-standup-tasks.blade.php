<?php

declare(strict_types=1);

use App\Enums\StandupTaskStatus;
use App\Enums\TaskStatus;
use App\Events\TaskStatusChanged;
use App\Models\StandUpTask;
use App\Models\Standup;
use App\Models\Task;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public int $standupId;

    public function mount(int $standupId): void
    {
        $this->standupId = $standupId;
        $standup = Standup::query()->findOrFail($standupId);
        if ($standup->user_id !== auth()->id()) {
            abort(403);
        }
    }

    #[Computed]
    public function standup(): Standup
    {
        return Standup::query()
            ->with(['standupTasks.task'])
            ->findOrFail($this->standupId);
    }

    #[Computed]
    public function completedStandupTasks(): \Illuminate\Support\Collection
    {
        return $this->standup->standupTasks->where('status', StandupTaskStatus::Completed);
    }

    #[Computed]
    public function plannedStandupTasks(): \Illuminate\Support\Collection
    {
        return $this->standup->standupTasks->where('status', StandupTaskStatus::Planned);
    }

    #[Computed]
    public function ongoingStandupTasks(): \Illuminate\Support\Collection
    {
        return $this->standup->standupTasks->where('status', StandupTaskStatus::Ongoing);
    }

    #[Computed]
    public function blockedStandupTasks(): \Illuminate\Support\Collection
    {
        return $this->standup->standupTasks->where('status', StandupTaskStatus::Blocked);
    }

    public function markComplete(int $taskId): void
    {
        $this->updateStandupTaskStatus($taskId, StandupTaskStatus::Completed, TaskStatus::Completed);
    }

    public function uncomplete(int $taskId): void
    {
        $this->updateStandupTaskStatus($taskId, StandupTaskStatus::Ongoing, TaskStatus::InProgress);
    }

    public function startTask(int $taskId): void
    {
        $this->updateStandupTaskStatus($taskId, StandupTaskStatus::Ongoing, TaskStatus::InProgress);
    }

    public function toggleBlocked(int $taskId): void
    {
        $standupTask = StandUpTask::query()
            ->where('standup_id', $this->standupId)
            ->where('task_id', $taskId)
            ->firstOrFail();

        if ($standupTask->status === StandupTaskStatus::Blocked) {
            $this->updateStandupTaskStatus($taskId, StandupTaskStatus::Planned, TaskStatus::InProgress);
        } else {
            $this->updateStandupTaskStatus($taskId, StandupTaskStatus::Blocked, TaskStatus::Blocked);
        }
    }

    protected function updateStandupTaskStatus(int $taskId, StandupTaskStatus $standupStatus, TaskStatus $taskStatus): void
    {
        $standupTask = StandUpTask::query()
            ->where('standup_id', $this->standupId)
            ->where('task_id', $taskId)
            ->firstOrFail();

        if ($standupTask->standup->user_id !== auth()->id()) {
            abort(403);
        }

        $standupTask->update(['status' => $standupStatus]);

        $task = Task::query()->findOrFail($taskId);
        $oldStatus = $task->status;
        if ($oldStatus !== $taskStatus) {
            $task->update(['status' => $taskStatus]);

            $actor = auth()->user();
            if ($actor instanceof \App\Models\User && $task->created_by !== $actor->id) {
                TaskStatusChanged::dispatch($task->fresh(['assignee']), $oldStatus, $taskStatus, $actor);
            }
        }

        unset($this->standup, $this->completedStandupTasks, $this->plannedStandupTasks, $this->ongoingStandupTasks, $this->blockedStandupTasks);
    }

};
?>

<div class="space-y-2 text-sm">
    @php
        $completed = $this->completedStandupTasks;
        $planned = $this->plannedStandupTasks;
        $ongoing = $this->ongoingStandupTasks;
        $blocked = $this->blockedStandupTasks;
    @endphp

    @if($completed->isNotEmpty())
        <div>
            <flux:text variant="subtle" class="mb-1 block">Completed</flux:text>
            <ul class="space-y-1">
                @foreach($completed as $standupTask)
                    @if($standupTask->task)
                        <li wire:key="st-completed-{{ $standupTask->id }}" class="flex items-center gap-2 group">
                            <flux:checkbox :checked="true"
                                           wire:click="uncomplete({{ $standupTask->task_id }})"
                                           class="!cursor-pointer"
                                           aria-label="Mark {{ $standupTask->task->title }} as not complete" />
                            <span class="flex-1 line-through opacity-70">{{ $standupTask->task->title }}</span>
                            <flux:button variant="ghost" size="xs" wire:click="uncomplete({{ $standupTask->task_id }})" wire:loading.attr="disabled">
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
                @foreach($ongoing as $standupTask)
                    @if($standupTask->task)
                        <li wire:key="st-ongoing-{{ $standupTask->id }}" class="flex items-center gap-2 group">
                            <flux:checkbox :checked="false"
                                           wire:click="markComplete({{ $standupTask->task_id }})"
                                           class="!cursor-pointer"
                                           aria-label="Mark {{ $standupTask->task->title }} complete" />
                            <span class="flex-1">{{ $standupTask->task->title }}</span>
                            <flux:button variant="ghost" size="xs" wire:click="toggleBlocked({{ $standupTask->task_id }})" wire:loading.attr="disabled" title="Mark as blocked">
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
                @foreach($planned as $standupTask)
                    @if($standupTask->task)
                        <li wire:key="st-planned-{{ $standupTask->id }}" class="flex items-center gap-2 group">
                            <span class="w-4 flex-shrink-0" aria-hidden="true"></span>
                            <span class="flex-1">{{ $standupTask->task->title }}</span>
                            <div class="flex items-center gap-1">
                                <flux:button variant="ghost" size="xs" wire:click="startTask({{ $standupTask->task_id }})" wire:loading.attr="disabled" title="Start">
                                    Start
                                </flux:button>
                                <flux:button variant="ghost" size="xs" wire:click="toggleBlocked({{ $standupTask->task_id }})" wire:loading.attr="disabled" title="Mark as blocked">
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
                    @foreach($blocked as $standupTask)
                        @if($standupTask->task)
                            <li wire:key="st-blocked-{{ $standupTask->id }}" class="flex items-center gap-2 group">
                                <span class="w-4 flex-shrink-0" aria-hidden="true"></span>
                                <span class="flex-1">{{ $standupTask->task->title }}</span>
                                <flux:button variant="ghost" size="xs" wire:click="toggleBlocked({{ $standupTask->task_id }})" wire:loading.attr="disabled" title="Remove blocker">
                                    Unblock
                                </flux:button>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($completed->isEmpty() && $planned->isEmpty() && $ongoing->isEmpty() && $blocked->isEmpty())
        <flux:text variant="subtle" class="italic">No tasks linked to this standup.</flux:text>
    @endif
</div>
