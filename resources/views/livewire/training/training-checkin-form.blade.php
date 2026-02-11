<div class="max-w-2xl mx-auto py-8">
    <div class="mb-8">
        <flux:heading size="xl" level="1">{{ __('Log Training Progress') }}</flux:heading>
        <flux:subheading>{{ $goal->title }}</flux:subheading>
    </div>

    <flux:card class="p-6">
        <form wire:submit="save" class="space-y-6">
            <flux:textarea wire:model="progress_update" :label="__('What did you work on?')" placeholder="Summarize your progress today..." required />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input type="number" wire:model="minutes_logged" :label="__('Time Spent (minutes)')" icon="clock" />
                
                <flux:select wire:model="confidence_level" :label="__('Confidence Level')">
                    @foreach($this->confidenceLevels as $level)
                        <option value="{{ $level->value }}">{{ $level->emoji() }} {{ $level->label() }}</option>
                    @endforeach
                </flux:select>
            </div>

            <flux:select wire:model="milestone_id" :label="__('Associated Milestone (Optional)')">
                <option value="">{{ __('General Progress') }}</option>
                @foreach($this->milestones as $m)
                    <option value="{{ $m->id }}">{{ $m->title }}</option>
                @endforeach
            </flux:select>

            <flux:separator variant="subtle" />

            <flux:heading level="3">{{ __('Additional Details (Optional)') }}</flux:heading>
            
            <flux:textarea wire:model="learnings" :label="__('Key Learnings')" placeholder="What new concepts did you pick up?" />
            <flux:textarea wire:model="blockers" :label="__('Blockers / Challenges')" placeholder="Anything holding you back?" />
            <flux:textarea wire:model="next_steps" :label="__('Next Steps')" placeholder="What's the plan for next time?" />

            <div class="flex justify-between items-center pt-6">
                <flux:button variant="ghost" href="{{ route('training.goals.show', $goal->slug) }}">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" type="submit" icon="check">{{ __('Log Progress') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
