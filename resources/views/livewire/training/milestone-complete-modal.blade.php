<div class="p-6">
    <flux:heading size="lg" class="mb-2">{{ __('Complete Milestone') }}</flux:heading>
    <flux:subheading class="mb-6">
        {{ $milestone->title }}
    </flux:subheading>

    <form wire:submit="submit" class="space-y-6">
        <flux:textarea wire:model="notes" :label="__('Completion Notes')" placeholder="How did you achieve this? Any key takeaways?" required />
        
        <flux:input wire:model="evidence_url" :label="__('Evidence URL (Optional)')" placeholder="Link to PR, doc, or repo" icon="link" />

        <div class="flex gap-2 justify-end">
            <flux:button variant="ghost" wire:click="$dispatch('closeModal')">{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" type="submit">{{ __('Mark as Complete') }}</flux:button>
        </div>
    </form>
</div>
