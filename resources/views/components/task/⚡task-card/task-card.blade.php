@php
    $priorityBarColor = match($task->priority->value) {
        'urgent' => 'bg-red-500',
        'high' => 'bg-orange-500',
        'medium' => 'bg-blue-500',
        'low' => 'bg-zinc-400',
        default => 'bg-zinc-400'
    };
    $cardClass = 'group relative rounded-lg border border-zinc-200/80 dark:border-zinc-700/80 bg-white dark:bg-zinc-800/90 shadow-sm hover:shadow-md hover:border-zinc-300 dark:hover:border-zinc-600/80 transition-all duration-200 overflow-hidden '
        . ($task->isOverdue() ? 'border-l-[3px] border-l-red-500 ' : '')
        . ($task->isDueToday() && !$task->isOverdue() ? 'border-l-[3px] border-l-amber-500 ' : '')
        . ($task->isDueSoon() && !$task->isDueToday() && !$task->isOverdue() ? 'border-l-[3px] border-l-blue-500 ' : '')
        . ($compact ? '!p-2' : '!p-3');
    $cardAttrs = $draggable && $this->canEdit && !$sortHandle
        ? ' draggable="true" x-data x-on:dragstart="$event.dataTransfer.setData(\'taskId\', '.$task->id.')"'
        : '';
@endphp
<div {!! $cardAttrs !!} class="{{ $cardClass }}">
    {{-- Priority bar (Trello-like) --}}
    <div class="h-0.5 {{ $priorityBarColor }} w-full flex-shrink-0"></div>
    <div class="{{ $compact ? 'p-2' : 'p-3' }} min-w-0">
    {{-- Priority & Title Row --}}
    <div class="flex items-start gap-2 {{ $compact ? 'mb-1' : 'mb-2' }}">
        {{-- One-click complete checkbox (always visible when editable) --}}
        @if($showActions && $this->canEdit)
            <button type="button"
                    wire:sort:ignore
                    wire:click.stop="toggleComplete"
                    wire:loading.attr="disabled"
                    class="flex-shrink-0 mt-0.5 p-2 rounded hover:bg-zinc-100 dark:hover:bg-zinc-700 transition focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500 focus:ring-offset-1 min-h-[44px] min-w-[44px]"
                    title="{{ $task->status === \App\Enums\TaskStatus::Completed ? 'Mark not complete' : 'Mark complete' }}"
                    aria-label="{{ $task->status === \App\Enums\TaskStatus::Completed ? 'Mark not complete' : 'Mark complete' }}">
                @if($task->status === \App\Enums\TaskStatus::Completed)
                    <flux:icon name="circle-check" variant="mini" class="text-green-500 size-5" />
                @else
                    <flux:icon name="circle-dot" variant="mini" class="text-zinc-400 dark:text-zinc-500 size-5 hover:text-zinc-600 dark:hover:text-zinc-400" />
                @endif
            </button>
        @endif

        {{-- Priority Icon --}}
        @php
            $priorityIcon = match($task->priority->value) {
                'urgent' => 'circle-alert',
                'high' => 'circle-arrow-up',
                'medium' => 'menu',
                'low' => 'arrow-down',
                default => 'minus'
            };
            $priorityColor = match($task->priority->value) {
                'urgent' => 'text-red-500',
                'high' => 'text-orange-500',
                'medium' => 'text-blue-500',
                'low' => 'text-zinc-500',
                default => 'text-zinc-400'
            };
        @endphp
        <flux:icon :name="$priorityIcon" variant="mini" class="flex-shrink-0 mt-0.5 {{ $priorityColor }}" title="{{ $task->priority->label() }}" />

        {{-- Title (click to open detail) --}}
        <button type="button"
                wire:sort:ignore
                wire:click.stop="$dispatch('open-task-detail', { taskId: {{ $task->id }} })"
                class="flex-1 min-w-0 text-left cursor-pointer hover:underline focus:outline-none focus:ring-0">
            <flux:heading level="4" class="{{ $compact ? '!text-xs' : '!text-sm' }} font-medium line-clamp-2">
                {{ $task->title }}
            </flux:heading>
        </button>

        {{-- One-click Start (Todo/Backlog only) --}}
        @if($showActions && $this->canEdit && in_array($task->status->value, ['backlog', 'todo'], true))
            <flux:button variant="ghost" size="xs" icon="play" wire:sort:ignore wire:click.stop="startTask" wire:loading.attr="disabled" class="flex-shrink-0 !min-w-0 min-h-[36px] sm:min-h-0" title="Start task">
                Start
            </flux:button>
        @endif

        {{-- Quick Status Menu (on hover) --}}
        @if($showActions && $this->canEdit)
            <div class="flex-shrink-0 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity" wire:sort:ignore @click.stop>
                <flux:dropdown>
                    <flux:button variant="ghost" size="xs" icon="ellipsis" class="min-h-[44px] min-w-[44px]" />

                    <flux:menu>
                        <flux:menu.group heading="Move to...">
                            @foreach($this->availableStatuses as $status)
                                @php
                                    $menuIcon = match($status->value) {
                                        'backlog' => 'clipboard',
                                        'todo' => 'list',
                                        'in_progress' => 'rotate-cw',
                                        'review' => 'search',
                                        'completed' => 'circle-check',
                                        'blocked' => 'triangle-alert',
                                        'cancelled' => 'circle-x',
                                        default => 'list'
                                    };
                                @endphp
                                <flux:menu.item wire:click.stop="updateStatus('{{ $status->value }}')" :icon="$menuIcon">
                                    {{ $status->label() }}
                                </flux:menu.item>
                            @endforeach
                        </flux:menu.group>
                    </flux:menu>
                </flux:dropdown>
            </div>
        @endif
    </div>

    {{-- Description --}}
    @if(!$compact && $task->description)
        <flux:text size="xs" class="line-clamp-2 mb-3 ml-6">
            {{ $task->description }}
        </flux:text>
    @endif

    {{-- Tags Row --}}
    <div class="flex flex-wrap items-center gap-1.5 {{ $compact ? 'mb-1.5 ml-6' : 'mb-3 ml-6' }}">
        {{-- Status Badge --}}
        @php
            $statusColor = match($task->status->value) {
                'backlog' => 'zinc',
                'todo' => 'blue',
                'in_progress' => 'orange',
                'review' => 'purple',
                'completed' => 'green',
                'blocked' => 'red',
                'cancelled' => 'zinc',
                default => 'zinc'
            };
        @endphp
        <flux:badge size="sm" :color="$statusColor" inset="top bottom">
            {{ $task->status->label() }}
        </flux:badge>

        {{-- Overdue Badge --}}
        @if($task->isOverdue())
            <flux:badge size="sm" color="red" icon="triangle-alert">Overdue</flux:badge>
        @endif
    </div>

    {{-- Footer --}}
    <div class="flex items-center justify-between gap-2 {{ $compact ? 'mt-1.5 ml-6' : 'mt-2 ml-6' }}">
        {{-- Subtask count (Trello-like) --}}
        @if(($task->subtasks_count ?? 0) > 0)
            <span class="flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400" title="Subtasks">
                <flux:icon name="list" class="size-3.5" />
                {{ $task->subtasks_completed_count ?? 0 }}/{{ $task->subtasks_count ?? 0 }}
            </span>
        @endif
        <div class="flex items-center gap-1.5 min-w-0 ml-auto">
            @if($task->assignee)
                <flux:tooltip :content="$task->assignee->name . ($task->assignee->id === auth()->id() ? ' (you)' : '')">
                    <img src="{{ $task->assignee->profileImageUrl('thumb') }}"
                         alt=""
                         class="size-6 rounded-full object-cover ring-1 ring-zinc-200 dark:ring-zinc-600 flex-shrink-0"
                    />
                </flux:tooltip>
                @if(!$compact)
                    <flux:text size="xs" class="truncate max-w-[72px]">
                        {{ $task->assignee->id === auth()->id() ? 'You' : $task->assignee->name }}
                    </flux:text>
                @endif
            @else
                <flux:text size="xs" variant="subtle" class="italic">Unassigned</flux:text>
            @endif
        </div>

        {{-- Due Date --}}
        @if($task->due_date)
            @php
                $dueColor = 'zinc';
                if ($task->isOverdue()) $dueColor = 'red';
                elseif ($task->isDueToday()) $dueColor = 'amber';
                elseif ($task->isDueSoon()) $dueColor = 'blue';

                $dueText = $task->due_date->format('M j');
                if ($task->isDueToday()) $dueText = 'Today';
                elseif ($task->due_date->isYesterday()) $dueText = 'Yesterday';
                elseif ($task->due_date->isTomorrow()) $dueText = 'Tomorrow';
                elseif ($task->isOverdue()) $dueText = $task->due_date->diffForHumans();
            @endphp
            <flux:badge size="sm" :color="$dueColor" icon="calendar" variant="pill">
                {{ $dueText }}
            </flux:badge>
        @endif
    </div>

    {{-- Standup Count --}}
    @php
        $standupCount = $task->standups_count ?? 0;
    @endphp
    @if($standupCount > 0)
        <div class="{{ $compact ? 'mt-1.5 ml-6' : 'mt-2 ml-6' }}">
            <flux:badge size="sm" color="indigo" icon="messages-square" variant="pill">
                {{ $standupCount }} standup(s)
            </flux:badge>
        </div>
    @endif
    </div>
</div>
