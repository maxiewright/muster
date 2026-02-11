<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile Settings') }}</flux:heading>

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <div class="my-6 w-full space-y-6">
            {{-- Profile image (Spatie Media + Gravatar fallback) --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex-shrink-0">
                    <img src="{{ auth()->user()->profileImageUrl('avatar') }}"
                         alt=""
                         class="size-20 rounded-full object-cover ring-2 ring-zinc-200 dark:ring-zinc-600"
                    />
                </div>
                <div class="flex flex-col gap-2 w-full">
                    <flux:input type="file"
                                wire:model="avatar"
                                accept="image/jpeg,image/png,image/webp,image/gif"
                                label="Profile photo"
                    />
                    @if(auth()->user()->getFirstMedia('avatar'))
                        <flux:button type="button" variant="ghost" size="sm" wire:click="removeAvatar" class="self-start">
                            Remove photo
                        </flux:button>
                    @endif
                    @if($avatar)
                        <flux:button type="button" variant="primary" size="sm" wire:click="updateAvatar" wire:loading.attr="disabled" class="self-start">
                            Upload
                        </flux:button>
                    @endif
                    @error('avatar')
                        <flux:error name="avatar" />
                    @enderror
                </div>
            </div>

            <form wire:submit="updateProfileInformation" class="space-y-6">
                <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

                <div>
                    <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                    @if ($this->hasUnverifiedEmail)
                        <div>
                            <flux:text class="mt-4">
                                {{ __('Your email address is unverified.') }}

                                <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                    {{ __('Click here to re-send the verification email.') }}
                                </flux:link>
                            </flux:text>

                            @if (session('status') === 'verification-link-sent')
                                <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </flux:text>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <flux:button variant="primary" type="submit" class="w-full sm:w-auto">{{ __('Save') }}</flux:button>
                    <x-action-message class="me-3" on="profile-updated">
                        {{ __('Saved.') }}
                    </x-action-message>
                </div>
            </form>
        </div>

        @if ($this->showDeleteUser)
            <livewire:settings.delete-user-form />
        @endif
    </x-settings.layout>
</section>
