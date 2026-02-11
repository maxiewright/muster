<div class="flex flex-col h-full bg-zinc-50 dark:bg-zinc-900/50 rounded-xl border border-zinc-200 dark:border-zinc-700/50 w-full min-w-0 md:min-w-[280px] md:w-80 md:flex-shrink-0">
    
    {{-- Column Header --}}
    <div class="p-4 border-b border-zinc-200 dark:border-zinc-700/50 flex items-center justify-between sticky top-0 bg-inherit z-10 rounded-t-xl backdrop-blur-sm">
        <div class="flex items-center gap-2">
            <div class="p-1.5 rounded-lg bg-white dark:bg-zinc-800 shadow-sm ring-1 ring-zinc-900/5 dark:ring-white/10">
                <flux:icon :name="$status->icon()" class="size-4 text-zinc-500 dark:text-zinc-400" />
            </div>
            <flux:heading level="4">{{ $status->label() }}</flux:heading>
            <flux:badge size="sm" variant="pill" color="zinc">{{ $tasks->count() }}</flux:badge>
        </div>
        
    <flux:dropdown>
        <flux:button variant="ghost" size="sm" icon="ellipsis" class="min-h-[44px] min-w-[44px]" />
            <flux:menu>
                <flux:menu.item icon="plus" wire:click="$dispatch('open-create-task-modal', { status: '{{ $status->value }}' })">Add Task</flux:menu.item>
                <flux:menu.separator />
                <flux:menu.item icon="trash-2" variant="danger">Clear Completed</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </div>

    {{-- Column Body (Sortable List) --}}
    <div class="flex-1 p-3 overflow-y-auto min-h-[150px] space-y-3 custom-scrollbar"
         wire:sort.ghost="sortTask"
         wire:sort:group="tasks">
        @foreach($tasks as $task)
            <div wire:sort:item="{{ $task->id }}" wire:key="task-{{ $task->id }}">
                <livewire:task.task-card :task="$task" :key="'task-'.$task->id.'-'.$task->status->value" :sort-handle="true" wire:sort:handle />
            </div>
        @endforeach

        @if($tasks->isEmpty())
            <div class="h-full flex flex-col items-center justify-center text-center p-8 border-2 border-dashed border-zinc-200 dark:border-zinc-700/50 rounded-xl text-zinc-400 dark:text-zinc-600">
                <flux:icon name="plus" class="size-6 mb-2 opacity-50" />
                <p class="text-sm font-medium">No tasks</p>
                <p class="text-xs mt-1">Drop tasks here</p>
            </div>
        @endif
    </div>
</div>
