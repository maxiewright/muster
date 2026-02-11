<div>
    {{-- Board View: mobile-friendly scroll, full-width grid on lg/xl, Trello-style add card in each column --}}
    @if($view === 'board')
        <div class="flex gap-3 overflow-x-auto overflow-y-hidden pb-4 min-h-[420px] snap-x snap-mandatory scroll-smooth lg:grid lg:grid-cols-4 lg:gap-4 lg:overflow-visible lg:h-[calc(100vh-7rem)] lg:min-h-0">
            @foreach($this->boardColumns as $status)
                @php
                    $columnTasks = $this->tasksByStatus[$status->value] ?? collect();
                @endphp
                <div class="flex flex-col flex-shrink-0 w-[85vw] sm:w-[280px] min-h-[380px] snap-center lg:w-auto lg:min-w-0 lg:min-h-0 lg:max-h-full bg-zinc-50/80 dark:bg-zinc-900/50 rounded-xl border border-zinc-200 dark:border-zinc-700/50 shadow-sm">
                    <div class="px-3 py-2.5 lg:px-4 lg:py-3 border-b border-zinc-200 dark:border-zinc-700/50 flex items-center justify-between sticky top-0 bg-inherit z-10 rounded-t-xl backdrop-blur-sm flex-shrink-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="p-1.5 rounded-lg bg-white dark:bg-zinc-800 shadow-sm ring-1 ring-zinc-900/5 dark:ring-white/10 flex-shrink-0">
                                <flux:icon :name="$status->icon()" class="size-4 text-zinc-500 dark:text-zinc-400" />
                            </div>
                            <flux:heading level="4" class="!text-sm truncate">{{ $status->label() }}</flux:heading>
                            <flux:badge size="sm" variant="pill" color="zinc" class="flex-shrink-0">{{ $columnTasks->count() }}</flux:badge>
                        </div>
                        <flux:dropdown class="flex-shrink-0">
                            <flux:button variant="ghost" size="xs" icon="ellipsis" aria-label="Column options" class="min-h-[44px] min-w-[44px]" />
                            <flux:menu>
                                <flux:menu.item icon="plus" wire:click="$dispatch('open-create-task-modal', { status: '{{ $status->value }}' })">Add Task</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash-2" variant="danger">Clear Completed</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                    <div class="flex-1 p-2 overflow-y-auto min-h-0 space-y-2 custom-scrollbar"
                         wire:sort.ghost="sortTaskTo{{ \Illuminate\Support\Str::studly($status->value) }}"
                         wire:sort:group="tasks">
                        @foreach($columnTasks as $task)
                            <div wire:sort:item="{{ $task->id }}" wire:key="task-{{ $task->id }}">
                                <livewire:task.task-card :task="$task" :key="'task-'.$task->id.'-'.$task->status->value" :compact="true" :sort-handle="true" wire:sort:handle />
                            </div>
                        @endforeach
                        @if($columnTasks->isEmpty())
                            <button type="button"
                                    wire:click="$dispatch('open-create-task-modal', { status: '{{ $status->value }}' })"
                                    class="w-full min-h-[100px] flex flex-col items-center justify-center text-center p-6 border-2 border-dashed border-zinc-200 dark:border-zinc-700/50 rounded-xl text-zinc-500 dark:text-zinc-400 hover:border-zinc-300 dark:hover:border-zinc-600 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/30 hover:text-zinc-700 dark:hover:text-zinc-300 transition focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500 focus:ring-offset-2">
                                <flux:icon name="plus" class="size-6 mb-2 opacity-70" />
                                <span class="text-sm font-medium">Add a card</span>
                            </button>
                        @endif
                    </div>
                    {{-- Trello-style: "Add a card" always visible at bottom of column --}}
                    <div class="p-2 pt-0 flex-shrink-0 border-t border-zinc-100 dark:border-zinc-800/50">
                        <button type="button"
                                wire:click="$dispatch('open-create-task-modal', { status: '{{ $status->value }}' })"
                                class="flex items-center gap-2 w-full px-3 py-2.5 rounded-lg text-left text-sm text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200/80 dark:hover:bg-zinc-700/50 transition focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500 rounded-lg">
                            <flux:icon name="plus" class="size-4 flex-shrink-0" />
                            <span>Add a card</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <flux:modal name="create-task-modal" wire:model="showCreateModal" @close="closeModal" class="w-[calc(100vw-2rem)] sm:max-w-xl md:max-w-2xl mx-4 sm:mx-auto">
        <livewire:task.create-task-modal />
    </flux:modal>

    <flux:modal name="task-detail-modal" wire:model="showTaskDetailModal" @close="closeTaskDetail" class="w-[calc(100vw-2rem)] sm:max-w-2xl md:max-w-3xl mx-4 sm:mx-auto">
        @if($selectedTaskId)
            <livewire:task.task-detail-modal :task-id="$selectedTaskId" :key="'task-detail-'.$selectedTaskId" />
        @endif
    </flux:modal>
</div>
