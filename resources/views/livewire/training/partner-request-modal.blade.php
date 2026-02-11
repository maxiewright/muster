<div class="p-6">
    <flux:heading size="lg" class="mb-2">{{ __('Partner Request') }}</flux:heading>
    <flux:subheading class="mb-6">
        {{ $goal->user->name }} {{ __('invited you to be their accountability partner for this goal:') }}
    </flux:subheading>

    <flux:card class="p-4 mb-6 bg-zinc-50 dark:bg-zinc-900 border-l-4 border-blue-500">
        <div class="flex items-center gap-3 mb-2">
            <span class="text-2xl">{{ $goal->category->icon() }}</span>
            <flux:heading size="sm">{{ $goal->title }}</flux:heading>
        </div>
        <flux:text size="sm" class="line-clamp-3">{{ $goal->description }}</flux:text>
    </flux:card>

    <div class="space-y-6">
        <div>
            <flux:heading level="3" size="sm" class="mb-2">{{ __('What is expected of you?') }}</flux:heading>
            <flux:text size="sm">
                <ul class="list-disc pl-4 space-y-1">
                    <li>{{ __('Receive notifications of their progress check-ins.') }}</li>
                    <li>{{ __('Provide feedback and encouragement.') }}</li>
                    <li>{{ __('Verify completed milestones and final goals.') }}</li>
                    <li>{{ __('Earn points for your support and feedback!') }}</li>
                </ul>
            </flux:text>
        </div>

        <div x-data="{ declining: false }">
            <div x-show="!declining" class="flex gap-3">
                <flux:button variant="primary" class="flex-1" wire:click="accept">{{ __('Accept & Start Supporting') }}</flux:button>
                <flux:button variant="ghost" @click="declining = true">{{ __('Decline') }}</flux:button>
            </div>

            <div x-show="declining" class="space-y-4">
                <flux:textarea wire:model="decline_reason" :label="__('Reason for declining (Optional)')" placeholder="e.g. Too many responsibilities right now..." />
                <div class="flex gap-2">
                    <flux:button variant="primary" color="red" wire:click="decline">{{ __('Confirm Decline') }}</flux:button>
                    <flux:button variant="ghost" @click="declining = false">{{ __('Cancel') }}</flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
