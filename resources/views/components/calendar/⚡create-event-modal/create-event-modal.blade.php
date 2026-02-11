<div class="p-6">
    <div class="mb-6 pr-10">
        <flux:heading level="3">{{ $eventId ? 'Edit Event' : 'Create Event' }}</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-4">
        {{-- Title --}}
        <flux:input label="Event Title" wire:model="title" placeholder="Meeting with..." required />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Start Date/Time --}}
            <flux:input type="datetime-local" label="Start Time" wire:model="starts_at" required />

            {{-- End Date/Time --}}
            <flux:input type="datetime-local" label="End Time" wire:model="ends_at" />
        </div>

        {{-- Type --}}
        <flux:select label="Event Type" wire:model="type">
            @foreach(\App\Enums\EventType::cases() as $type)
                <flux:select.option value="{{ $type->value }}">
                    {{ $type->label() }}
                </flux:select.option>
            @endforeach
        </flux:select>

        {{-- Description --}}
        <flux:textarea label="Description" wire:model="description" placeholder="Event details..." rows="3" />

        {{-- Participants --}}
        <div>
            <flux:label>Participants</flux:label>
            <div class="rounded-lg ring-1 ring-zinc-200 dark:ring-zinc-700 bg-zinc-50/70 dark:bg-zinc-900/40 p-3 mt-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($this->teamMembers as $member)
                    <div class="flex items-center gap-2 p-2 rounded-lg hover:bg-white/70 dark:hover:bg-zinc-800/60">
                        <flux:checkbox wire:model="participants" value="{{ $member->id }}" label="{{ $member->name }}" />
                    </div>
                @endforeach
            </div>
            @error('participants') <flux:error>{{ $message }}</flux:error> @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <div>
                @if($eventId)
                    <flux:button variant="danger" wire:click="delete" wire:confirm="Are you sure you want to delete this event?">
                        Delete
                    </flux:button>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">
                    <span wire:loading.remove wire:target="save">
                        {{ $eventId ? 'Update Event' : 'Create Event' }}
                    </span>
                    <span wire:loading wire:target="save">
                        Saving...
                    </span>
                </flux:button>
            </div>
        </div>
    </form>
</div>
