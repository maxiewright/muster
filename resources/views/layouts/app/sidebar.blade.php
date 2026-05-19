<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-zinc-900 tactical-bg dark:bg-zinc-950 dark:text-zinc-100">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/80 dark:backdrop-blur-md">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                {{-- Pulsing LIVE indicator when the active unit has musters today --}}
                @if(\App\Models\Muster::query()->inUnit($activeUnit?->id ?? null)->whereDate('date', today())->exists())
                    <span class="relative ml-1 flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    </span>
                @endif
                @if(isset($activeUnit) && $activeUnit)
                    <div class="ml-3 hidden min-w-0 lg:block">
                        <p class="truncate text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">{{ $activeUnit->organization->name }}</p>
                        <p class="truncate text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $activeUnit->name }}</p>
                    </div>
                @endif
                <flux:spacer />
                <div class="hidden lg:block">
                    <livewire:training.partner-notifications-dropdown />
                </div>
                <flux:sidebar.collapse class="lg:hidden min-h-[44px] min-w-[44px]" />
            </flux:sidebar.header>

            @if(isset($availableUnits) && $availableUnits->isNotEmpty())
                <div class="px-3 pb-2">
                    <form method="POST" action="{{ route('units.active') }}">
                        @csrf
                        <flux:field>
                            <flux:label class="text-xs uppercase tracking-[0.18em] !text-zinc-400">Active Unit</flux:label>
                            <flux:select name="unit_id" onchange="this.form.submit()">
                                @foreach($availableUnits as $unit)
                                    <flux:select.option value="{{ $unit->id }}" :selected="$activeUnit?->id === $unit->id">
                                        {{ $unit->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    </form>
                </div>
            @endif

            <flux:sidebar.nav>

                <flux:sidebar.group :heading="__('Overview')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Operations')" class="grid">
                    <flux:sidebar.item icon="person-standing" :href="route('musters')" :current="request()->routeIs('musters') || request()->routeIs('muster.*')" wire:navigate>
                        {{ __('Musters') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="flag" :href="route('missions.index')" :current="request()->routeIs('missions.*')" wire:navigate>
                        {{ __('Missions') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-list" :href="route('tasks')" :current="request()->routeIs('tasks')" wire:navigate>
                        {{ __('Actions') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="graduation-cap" :href="route('training.dashboard')" :current="request()->routeIs('training.*')" wire:navigate>
                        {{ __('Training') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar" :href="route('calendar')" :current="request()->routeIs('calendar')" wire:navigate>
                        {{ __('Calendar') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Progress')" class="grid">
                    <flux:sidebar.item icon="trophy" :href="route('gamification')" :current="request()->routeIs('gamification')" wire:navigate>
                        {{ __('Achievements') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if(auth()->user()->canCreateUnits() || auth()->user()->canInviteMembers($activeUnit ?? null))
                    <flux:sidebar.group :heading="__('Team')" class="grid">
                        @if(auth()->user()->canCreateUnits())
                            <flux:sidebar.item icon="building-office-2" :href="route('team.units.index')" :current="request()->routeIs('team.units.*')" wire:navigate>
                                {{ __('Units') }}
                            </flux:sidebar.item>
                        @endif
                        @if(auth()->user()->canInviteMembers($activeUnit ?? null))
                            <flux:sidebar.item icon="users" :href="route('team.invitations')" :current="request()->routeIs('team.invitations')" wire:navigate>
                                {{ __('Invitations') }}
                            </flux:sidebar.item>
                        @endif
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            {{-- XP & Rank Bar --}}
            <livewire:xp-rank-bar />

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

        {{-- Mobile Bottom Navigation --}}
        <nav class="fixed inset-x-0 bottom-0 z-50 flex items-center justify-around border-t border-zinc-200 bg-white/90 backdrop-blur-lg pb-safe-bottom dark:border-zinc-800 dark:bg-zinc-900/90 lg:hidden" style="min-height: 56px;">
            <a href="{{ $myMusterRoute = auth()->user()->todaysMuster() ? route('muster.edit', auth()->user()->todaysMuster()) : route('muster.create') }}" wire:navigate class="group flex flex-1 flex-col items-center gap-0.5 py-2 transition-transform active:scale-95 {{ request()->routeIs('musters') || request()->routeIs('muster.*') ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400 dark:text-zinc-500' }}">
                <flux:icon.person-standing class="size-5" />
                <span class="text-[10px] font-medium">Muster</span>
            </a>
            <a href="{{ route('missions.index') }}" wire:navigate class="group flex flex-1 flex-col items-center gap-0.5 py-2 transition-transform active:scale-95 {{ request()->routeIs('missions.*') ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400 dark:text-zinc-500' }}">
                <flux:icon.flag class="size-5" />
                <span class="text-[10px] font-medium">Missions</span>
            </a>
            <a href="{{ route('tasks') }}" wire:navigate class="group flex flex-1 flex-col items-center gap-0.5 py-2 transition-transform active:scale-95 {{ request()->routeIs('tasks') ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400 dark:text-zinc-500' }}">
                <flux:icon.clipboard-list class="size-5" />
                <span class="text-[10px] font-medium">Actions</span>
            </a>
            <a href="{{ route('training.dashboard') }}" wire:navigate class="group flex flex-1 flex-col items-center gap-0.5 py-2 transition-transform active:scale-95 {{ request()->routeIs('training.*') ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400 dark:text-zinc-500' }}">
                <flux:icon.graduation-cap class="size-5" />
                <span class="text-[10px] font-medium">Training</span>
            </a>
            <a href="{{ route('calendar') }}" wire:navigate class="group flex flex-1 flex-col items-center gap-0.5 py-2 transition-transform active:scale-95 {{ request()->routeIs('calendar') ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400 dark:text-zinc-500' }}">
                <flux:icon.calendar class="size-5" />
                <span class="text-[10px] font-medium">Calendar</span>
            </a>
        </nav>

        @fluxScripts(['nonce' => \Illuminate\Support\Facades\Vite::cspNonce()])
    </body>
</html>
