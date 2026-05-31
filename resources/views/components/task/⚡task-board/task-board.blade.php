@php
    $statusValues = collect($this->boardColumns)->map(fn ($s) => $s->value)->all();
@endphp
<div x-data="{
        activeStatus: '{{ $statusValues[0] }}',
        statuses: @js($statusValues),
        init() {
            const saved = sessionStorage.getItem('muster:taskBoard:activeStatus');
            if (saved && this.statuses.includes(saved)) {
                this.activeStatus = saved;
            }
            this.$watch('activeStatus', (v) => sessionStorage.setItem('muster:taskBoard:activeStatus', v));
        }
    }">
    @if($view === 'board')
        {{-- Mobile-only status tabs: segmented control. Hidden on lg+ where the kanban shows all columns. --}}
        <div class="-mx-3 mb-3 overflow-x-auto pb-1 lg:hidden">
            <div class="flex min-w-full gap-2 px-3">
                @foreach($this->boardColumns as $status)
                    @php $columnCount = ($this->tasksByStatus[$status->value] ?? collect())->count(); @endphp
                    <button
                        type="button"
                        @click="activeStatus = '{{ $status->value }}'"
                        :class="activeStatus === '{{ $status->value }}'
                            ? 'bg-emerald-600 text-white border-emerald-600 dark:bg-emerald-500 dark:border-emerald-500'
                            : 'bg-white text-zinc-700 border-zinc-200 dark:bg-zinc-900 dark:text-zinc-200 dark:border-zinc-700'"
                        class="inline-flex min-h-[44px] flex-shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full border px-3.5 py-2 text-xs font-semibold transition active:scale-[0.97]">
                        <flux:icon :name="$status->icon()" class="size-3.5" />
                        {{ $status->label() }}
                        <span :class="activeStatus === '{{ $status->value }}'
                                ? 'bg-white/25 text-white'
                                : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400'"
                            class="ml-0.5 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-[10px] font-bold tabular-nums">
                            {{ $columnCount }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- On mobile, only the active column renders (single-column list, full width).
             On lg+, all four columns become a grid. --}}
        <div class="flex flex-col gap-3 lg:grid lg:grid-cols-4 lg:gap-4 lg:h-[calc(100vh-7rem)]">
            @foreach($this->boardColumns as $status)
                @php
                    $columnTasks = $this->tasksByStatus[$status->value] ?? collect();
                @endphp
                <div
                    x-show="activeStatus === '{{ $status->value }}' || window.matchMedia('(min-width: 1024px)').matches"
                    x-cloak
                    class="flex w-full flex-col rounded-xl border border-zinc-200 bg-zinc-50/80 shadow-sm dark:border-zinc-700/50 dark:bg-zinc-900/50 lg:min-h-0 lg:max-h-full">
                    <div class="flex items-center justify-between rounded-t-xl border-b border-zinc-200 bg-inherit px-3 py-2.5 backdrop-blur-sm dark:border-zinc-700/50 lg:px-4 lg:py-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <div class="flex-shrink-0 rounded-lg bg-white p-1.5 shadow-sm ring-1 ring-zinc-900/5 dark:bg-zinc-800 dark:ring-white/10">
                                <flux:icon :name="$status->icon()" class="size-4 text-zinc-500 dark:text-zinc-400" />
                            </div>
                            <flux:heading level="4" class="!text-sm truncate">{{ $status->label() }}</flux:heading>
                            <flux:badge size="sm" variant="pill" color="zinc" class="flex-shrink-0 tabular-nums">{{ $columnTasks->count() }}</flux:badge>
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
                    <div class="custom-scrollbar flex-1 space-y-1.5 overflow-y-auto p-1.5 sm:space-y-2 sm:p-2 lg:min-h-0">
                        @foreach($columnTasks as $task)
                            <div wire:key="task-{{ $task->id }}">
                                <livewire:task.task-card :task="$task" :key="'task-'.$task->id.'-'.$task->status->value" :compact="true" />
                            </div>
                        @endforeach
                        @if($columnTasks->isEmpty())
                            <button type="button"
                                    wire:click="$dispatch('open-create-task-modal', { status: '{{ $status->value }}' })"
                                    class="flex min-h-[120px] w-full flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-200 p-6 text-center text-zinc-500 transition hover:border-zinc-300 hover:bg-zinc-100/50 hover:text-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-400 focus:ring-offset-2 dark:border-zinc-700/50 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-800/30 dark:hover:text-zinc-300 dark:focus:ring-zinc-500">
                                <flux:icon name="plus" class="mb-2 size-6 opacity-70" />
                                <span class="text-sm font-medium">No {{ strtolower($status->label()) }} tasks yet. Tap to add one.</span>
                            </button>
                        @endif
                    </div>
                    {{-- Always-visible "Add a card" CTA at the bottom of each column. --}}
                    <div class="flex-shrink-0 border-t border-zinc-100 p-2 dark:border-zinc-800/50">
                        <button type="button"
                                wire:click="$dispatch('open-create-task-modal', { status: '{{ $status->value }}' })"
                                class="flex min-h-[48px] w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm text-zinc-600 transition hover:bg-zinc-200/80 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:text-zinc-400 dark:hover:bg-zinc-700/50 dark:focus:ring-zinc-500">
                            <flux:icon name="plus" class="size-4 flex-shrink-0" />
                            <span>Add a card</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Floating action button on mobile: quick-create a task in the currently-selected status.
             Sits above the bottom tab nav (which is ~56px tall). --}}
        <button type="button"
                @click="$dispatch('open-create-task-modal', { status: activeStatus })"
                class="fixed bottom-20 right-4 z-40 inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-500 active:scale-95 lg:hidden"
                aria-label="Add new task">
            <flux:icon name="plus" class="size-6" />
        </button>
    @endif

    <flux:modal name="create-task-modal" wire:model="showCreateModal" @close="closeModal" class="w-[calc(100vw-1.5rem)] mx-3 sm:mx-auto sm:max-w-xl md:max-w-2xl">
        <livewire:task.create-task-modal />
    </flux:modal>

    <flux:modal name="task-detail-modal" wire:model="showTaskDetailModal" @close="closeTaskDetail" class="w-[calc(100vw-1.5rem)] mx-3 sm:mx-auto sm:max-w-2xl md:max-w-3xl">
        @if($selectedTaskId)
            <livewire:task.task-detail-modal :task-id="$selectedTaskId" :key="'task-detail-'.$selectedTaskId" />
        @endif
    </flux:modal>
</div>
