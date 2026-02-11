<div class="p-6">
    <div class="mb-6 pr-10">
        <flux:heading level="3">{{ $taskId ? 'Edit Task' : 'Create Task' }}</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-4">
        {{-- Title --}}
        <flux:input label="Title" wire:model="title" placeholder="Task title..." required />
        <flux:error name="title" />

        {{-- Description --}}
        <flux:textarea label="Description" wire:model="description" placeholder="Task description..." rows="3" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Status --}}
            <flux:select label="Status" wire:model="status">
                @foreach($this->statuses as $statusOption)
                    <flux:select.option value="{{ $statusOption->value }}" icon="{{ $statusOption->icon() }}">
                        {{ $statusOption->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            {{-- Priority --}}
            <flux:select label="Priority" wire:model="priority">
                @foreach($this->priorities as $priorityOption)
                    <flux:select.option value="{{ $priorityOption->value }}" icon="{{ $priorityOption->icon() }}">
                        {{ $priorityOption->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Assignee --}}
            <flux:select label="Assign To" wire:model="assigned_to" :disabled="!$this->canAssign && $assigned_to !== auth()->id()">
                <flux:select.option value="">Unassigned</flux:select.option>
                @foreach($this->teamMembers as $member)
                    <flux:select.option value="{{ $member->id }}" :disabled="!$this->canAssign && $member->id !== auth()->id()">
                        <div class="flex items-center gap-2">
                            <span>{{ $member->name }}</span>
                            @if($member->id === auth()->id()) <span class="text-xs text-zinc-500">(me)</span> @endif
                            @if($member->isLead()) <span>⭐</span> @endif
                        </div>
                    </flux:select.option>
                @endforeach
            </flux:select>
            @if(!$this->canAssign)
                <flux:text size="xs" variant="subtle" class="mt-1">
                    You can only assign tasks to yourself. Team leads can assign to anyone.
                </flux:text>
            @endif

            {{-- Due Date --}}
            <flux:input type="date" label="Due Date" wire:model="due_date" />
        </div>

        {{-- Notes --}}
        <flux:textarea label="Notes" wire:model="notes" placeholder="Additional notes..." rows="2" />

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <div>
                @if($taskId && $this->canDelete)
                    <flux:button variant="danger" wire:click="delete" wire:confirm="Are you sure you want to delete this task?">
                        Delete Task
                    </flux:button>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">
                    <span wire:loading.remove wire:target="save">
                        {{ $taskId ? 'Update Task' : 'Create Task' }}
                    </span>
                    <span wire:loading wire:target="save">
                        Saving...
                    </span>
                </flux:button>
            </div>
        </div>
    </form>

    {{-- Task History (for existing tasks) --}}
    @if($taskId && $this->task)
        <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
            <flux:heading level="4" size="sm" class="mb-2">Task Info</flux:heading>
            <flux:text size="xs" variant="subtle">
                Created by <strong>{{ $this->task->creator->name }}</strong> on {{ $this->task->created_at->format('M j, Y \a\t H:i') }}<br>
                Last updated {{ $this->task->updated_at->diffForHumans() }}
                @if($this->task->standups->isNotEmpty())
                    <br>Referenced in {{ $this->task->standups->count() }} standup(s)
                @endif
            </flux:text>
        </div>
    @endif
</div>
