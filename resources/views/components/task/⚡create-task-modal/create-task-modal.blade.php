<div class="p-6">
    <div class="mb-6 pr-10">
        <flux:heading level="3">{{ $taskId ? 'Edit Action' : 'Create Action' }}</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-4">
        {{-- Title --}}
        <flux:input label="Action Title" wire:model="title" placeholder="Action title..." required />
        <flux:error name="title" />

        {{-- Description --}}
        <flux:textarea label="Description" wire:model="description" placeholder="Action description..." rows="3" />
        <flux:error name="description" />

        <flux:field>
            <flux:label>Mission</flux:label>
            <flux:select wire:model="mission_id">
                @foreach($this->missions as $mission)
                    <flux:select.option value="{{ $mission->id }}">{{ $mission->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="mission_id" />
        </flux:field>

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
            <flux:select label="Action Lead" wire:model="assigned_to" :disabled="!$this->canAssign && $assigned_to !== auth()->id()">
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
                    You can only assign actions to yourself. Team leads can assign to anyone.
                </flux:text>
            @endif

            {{-- Due Date --}}
            <flux:input type="date" label="Due Date" wire:model="due_date" />
        </div>

        <flux:field>
            <flux:label>Assigned Members</flux:label>
            <div class="mt-2 grid gap-2 md:grid-cols-2">
                @foreach($this->teamMembers as $member)
                    <label class="flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                        <flux:checkbox wire:model="assigned_members" value="{{ $member->id }}" />
                        <span>{{ $member->name }}</span>
                    </label>
                @endforeach
            </div>
            <flux:error name="assigned_members" />
        </flux:field>

        {{-- Notes --}}
        <flux:textarea label="Notes" wire:model="notes" placeholder="Additional notes..." rows="2" />

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <div>
                @if($taskId && $this->canDelete)
                    <flux:button variant="danger" wire:click="delete" wire:confirm="Are you sure you want to delete this action?">
                        Delete Action
                    </flux:button>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">
                    <span wire:loading.remove wire:target="save">
                        {{ $taskId ? 'Update Action' : 'Create Action' }}
                    </span>
                    <span wire:loading wire:target="save">
                        Saving...
                    </span>
                </flux:button>
            </div>
        </div>
    </form>

    {{-- Action History (for existing actions) --}}
    @if($taskId && $this->task)
        <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
            <flux:heading level="4" size="sm" class="mb-2">Action Info</flux:heading>
            <flux:text size="xs" variant="subtle">
                Created by <strong>{{ $this->task->creator->name }}</strong> on {{ $this->task->created_at->format('M j, Y \a\t H:i') }}<br>
                Last updated {{ $this->task->updated_at->diffForHumans() }}
                @if($this->task->musters->isNotEmpty())
                    <br>Referenced in {{ $this->task->musters->count() }} muster(s)
                @endif
            </flux:text>
        </div>
    @endif
</div>
