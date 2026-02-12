<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:spacer />
                <div class="hidden lg:block">
                    <livewire:training.partner-notifications-dropdown />
                </div>
                <flux:sidebar.collapse class="lg:hidden min-h-[44px] min-w-[44px]" />
            </flux:sidebar.header>

            <flux:sidebar.nav>

                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-list" :href="route('tasks')" :current="request()->routeIs('tasks')" wire:navigate>
                        {{ __('Tasks') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="person-standing" :href="route('standups')" :current="request()->routeIs('standups')" wire:navigate>
                        {{ __('Standup') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar" :href="route('calendar')" :current="request()->routeIs('calendar')" wire:navigate>
                        {{ __('Calendar') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="graduation-cap" :href="route('training.dashboard')" :current="request()->routeIs('training.*')" wire:navigate>
                        {{ __('Training') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Progress')" class="grid">
                    <flux:sidebar.item icon="trophy" :href="route('gamification')" :current="request()->routeIs('gamification')" wire:navigate>
                        {{ __('Achievements') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if(auth()->user()->isLead())
                    <flux:sidebar.group :heading="__('Team')" class="grid">
                        <flux:sidebar.item icon="users" :href="route('team.invitations')" :current="request()->routeIs('team.invitations')" wire:navigate>
                            {{ __('Invitations') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            {{-- Gamification strip --}}
            <div class="px-3 py-2 mx-3 mb-2 rounded-lg bg-gradient-to-br from-amber-500/10 to-orange-600/10 dark:from-amber-500/20 dark:to-orange-600/20 border border-amber-500/20">
                <a href="{{ route('gamification') }}" wire:navigate class="block">
                    <div class="flex items-center justify-between gap-2 text-sm">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="text-base" aria-hidden="true">🔥</span>
                            <span class="font-semibold text-amber-700 dark:text-amber-300 truncate">{{ auth()->user()->current_streak ?? 0 }}</span>
                            <span class="text-amber-600 dark:text-amber-400 truncate">day streak</span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <span class="text-base" aria-hidden="true">⭐</span>
                            <span class="font-semibold text-amber-700 dark:text-amber-300">{{ number_format(auth()->user()->points ?? 0) }}</span>
                        </div>
                    </div>
                </a>
            </div>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden min-h-[44px] min-w-[44px]" icon="menu" inset="left" />

            <flux:spacer />

            <livewire:training.partner-notifications-dropdown />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="settings" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="log-out"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
