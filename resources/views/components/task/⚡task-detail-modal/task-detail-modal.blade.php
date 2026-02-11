@php
    $task = $this->task;
@endphp
@if($task)
    <div class="p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div class="min-w-0 flex-1">
                <flux:heading level="2" class="break-words">{{ $task->title }}</flux:heading>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    @php
                        $statusColor = match($task->status->value) {
                            'backlog' => 'zinc', 'todo' => 'blue', 'in_progress' => 'orange',
                            'review' => 'purple', 'completed' => 'green', 'blocked' => 'red', default => 'zinc'
                        };
                    @endphp
                    <flux:badge size="sm" :color="$statusColor">
                        {{ $task->status->label() }}
                    </flux:badge>
                    <flux:badge size="sm" color="zinc">{{ $task->priority->label() }}</flux:badge>
                    @if($task->due_date)
                        <flux:badge size="sm" color="zinc" icon="calendar">{{ $task->due_date->format('M j, Y') }}</flux:badge>
                    @endif
                </div>
            </div>
            <flux:button variant="ghost" size="sm" icon="x" wire:click="close" aria-label="Close" class="min-h-[44px] min-w-[44px]" />
        </div>

        @if($task->description)
            <div class="mb-6">
                <flux:heading level="4" class="!text-sm text-zinc-500 dark:text-zinc-400 mb-1">Description</flux:heading>
                <flux:text class="whitespace-pre-wrap">{{ $task->description }}</flux:text>
            </div>
        @endif

        <div class="mb-4 flex items-center gap-2">
            @if($task->assignee)
                <flux:tooltip :content="$task->assignee->name">
                    <img src="{{ $task->assignee->profileImageUrl('thumb') }}"
                         alt=""
                         class="size-8 rounded-full object-cover ring-2 ring-white dark:ring-zinc-800"
                    />
                </flux:tooltip>
                <flux:text size="sm">{{ $task->assignee->name }}{{ $task->assignee->id === auth()->id() ? ' (you)' : '' }}</flux:text>
            @else
                <flux:text size="sm" variant="subtle">Unassigned</flux:text>
            @endif
        </div>

        {{-- Subtasks --}}
        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:heading level="4" class="!text-sm flex items-center gap-2 mb-3">
                <flux:icon name="list" class="size-4" />
                Subtasks
                @if($task->subtasksTotalCount() > 0)
                    <flux:badge size="sm" variant="pill" color="zinc">
                        {{ $task->subtasksCompletedCount() }}/{{ $task->subtasksTotalCount() }}
                    </flux:badge>
                @endif
            </flux:heading>

            <ul class="space-y-2 mb-4">
                @foreach($task->subtasks as $subtask)
                    <li wire:key="subtask-{{ $subtask->id }}"
                        class="flex items-center gap-3 p-2 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        @if($task->canBeEditedBy(auth()->user()))
                            <button type="button"
                                    wire:click="toggleSubtaskComplete({{ $subtask->id }})"
                                    class="flex-shrink-0 p-2 rounded focus:outline-none focus:ring-2 focus:ring-zinc-400 min-h-[44px] min-w-[44px]">
                                @if($subtask->status === \App\Enums\TaskStatus::Completed)
                                    <flux:icon name="circle-check" variant="mini" class="text-green-500 size-5" />
                                @else
                                    <flux:icon name="circle-dot" variant="mini" class="text-zinc-400 size-5" />
                                @endif
                            </button>
                        @endif
                        <span class="flex-1 {{ $subtask->status === \App\Enums\TaskStatus::Completed ? 'line-through text-zinc-500' : '' }}">
                            {{ $subtask->title }}
                        </span>
                        <flux:badge size="sm" color="zinc" class="hidden sm:inline-flex">{{ $subtask->status->label() }}</flux:badge>
                    </li>
                @endforeach
            </ul>

            @if($task->canBeEditedBy(auth()->user()))
                <form wire:submit="addSubtask" class="flex flex-col sm:flex-row gap-2">
                    <flux:input wire:model="newSubtaskTitle"
                                placeholder="Add a subtask..."
                                class="flex-1 min-w-0"
                                size="sm" />
                    <flux:button type="submit" size="sm" icon="plus" class="min-h-[44px] sm:min-h-0">Add</flux:button>
                </form>
                @error('newSubtaskTitle')
                    <flux:error name="newSubtaskTitle" class="mt-1" />
                @enderror
            @endif
        </div>
    </div>
@else
    <div class="p-6">
        <flux:text>Task not found or you don't have permission to view it.</flux:text>
        <flux:button wire:click="close" class="mt-4">Close</flux:button>
    </div>
@endif
