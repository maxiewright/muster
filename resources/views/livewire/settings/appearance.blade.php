<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Appearance Settings') }}</flux:heading>

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <form method="POST" wire:submit="updateTheme" class="space-y-6">
            <flux:radio.group wire:model.live="theme" variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                <flux:radio value="system" icon="monitor">{{ __('System') }}</flux:radio>
            </flux:radio.group>

            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <flux:button variant="primary" type="submit" class="w-full sm:w-auto">{{ __('Save') }}</flux:button>
                <x-action-message class="me-3" on="theme-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
