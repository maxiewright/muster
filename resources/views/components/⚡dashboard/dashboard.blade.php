<div class="space-y-6">
    {{-- Header: mobile-first stack, then row --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">Muster Board</flux:heading>
            <flux:subheading>{{ now()->format('l, F j, Y') }}</flux:subheading>
        </div>
        <div class="flex items-center gap-2 min-h-[44px]">
            <flux:badge color="green" size="sm" icon="circle-check">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse mr-1.5"></span>
                {{ $todaysStandups->count() }} checked in
            </flux:badge>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content: Standups (today-focused; task check-off is in the standup flow) --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- My Standup --}}
            @if(!$myStandup)
                <flux:card class="!bg-amber-50 dark:!bg-amber-900/20 !border-amber-200 dark:!border-amber-800">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl" aria-hidden="true">📋</span>
                            <div>
                                <flux:heading level="3" class="!text-amber-800 dark:!text-amber-200">You haven't checked in today</flux:heading>
                                <flux:text class="!text-amber-600 dark:!text-amber-400">Share what you're working on with the team</flux:text>
                            </div>
                        </div>
                        <flux:button href="{{ route('standup.create') }}" variant="primary" class="!bg-amber-600 hover:!bg-amber-700 !border-amber-600 min-h-[44px] min-w-[44px] shrink-0" wire:navigate>
                            Check In
                        </flux:button>
                    </div>
                </flux:card>
            @endif

            {{-- Team Standups --}}
            <flux:card class="p-0 overflow-hidden">
                <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
                    <flux:heading level="2" size="lg">Today's Muster</flux:heading>
                </div>

                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($todaysStandups as $standup)
                        <div class="p-4">
                            <div class="flex items-start gap-3">
                                <flux:avatar :name="$standup->user->name" variant="solid" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <flux:heading level="3">{{ $standup->user->name }}</flux:heading>
                                        @if($standup->mood)
                                            <flux:tooltip :content="$standup->mood->label()">
                                                <span class="text-lg cursor-help">{{ $standup->mood->emoji() }}</span>
                                            </flux:tooltip>
                                        @endif
                                        <flux:text class="text-xs">{{ $standup->created_at->format('H:i') }}</flux:text>
                                    </div>

                                    @if($standup->user_id === auth()->id())
                                        <livewire:muster-standup-tasks :standup-id="$standup->id" :key="'muster-'.$standup->id" />
                                    @else
                                        @php
                                            $completed = $standup->standupTasks->where('status', \App\Enums\StandupTaskStatus::Completed);
                                            $planned = $standup->standupTasks->where('status', \App\Enums\StandupTaskStatus::Planned);
                                            $blocked = $standup->standupTasks->where('status', \App\Enums\StandupTaskStatus::Blocked);
                                        @endphp

                                        <div class="space-y-2 text-sm">
                                            @if($completed->isNotEmpty())
                                                <div>
                                                    <flux:text variant="subtle">Completed:</flux:text>
                                                    <flux:text>
                                                        {{ $completed->take(3)->map(fn($s) => optional($s->task)->title)->filter()->implode(', ') }}
                                                        @if($completed->count() > 3)
                                                            +{{ $completed->count() - 3 }} more
                                                        @endif
                                                    </flux:text>
                                                </div>
                                            @endif

                                            @if($planned->isNotEmpty())
                                                <div>
                                                    <flux:text variant="subtle">Planned:</flux:text>
                                                    <flux:text>
                                                        {{ $planned->take(3)->map(fn($s) => optional($s->task)->title)->filter()->implode(', ') }}
                                                        @if($planned->count() > 3)
                                                            +{{ $planned->count() - 3 }} more
                                                        @endif
                                                    </flux:text>
                                                </div>
                                            @endif

                                            @if($blocked->isNotEmpty())
                                                <div class="flex items-start gap-1">
                                                    <span class="text-red-500">⚠️</span>
                                                    <flux:text class="!text-red-600 dark:!text-red-400">
                                                        {{ $blocked->take(3)->map(fn($s) => optional($s->task)->title)->filter()->implode(', ') }}
                                                        @if($blocked->count() > 3)
                                                            +{{ $blocked->count() - 3 }} more
                                                        @endif
                                                    </flux:text>
                                                </div>
                                            @endif

                                            @if($completed->isEmpty() && $planned->isEmpty() && $blocked->isEmpty())
                                                <div>
                                                    <flux:text variant="subtle" class="italic">No tasks linked to this standup.</flux:text>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if($standup->blockers)
                                            <div class="flex items-start gap-1">
                                                <span class="text-red-500">⚠️</span>
                                                <flux:text class="!text-red-600 dark:!text-red-400">{{ $standup->blockers }}</flux:text>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <span class="text-4xl mb-2 block">🏕️</span>
                            <flux:heading level="3">No one has checked in yet today.</flux:heading>
                            <flux:text>Be the first!</flux:text>
                        </div>
                    @endforelse
                </div>
            </flux:card>
        </div>

        {{-- Sidebar: Upcoming Events --}}
        <div class="space-y-4">
            <flux:card class="p-0 overflow-hidden">
                <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between bg-zinc-50 dark:bg-zinc-900/50">
                    <flux:heading level="2" size="lg">Upcoming Huddles</flux:heading>
                    <flux:link href="{{ route('calendar') }}" wire:navigate>View All →</flux:link>
                </div>

                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($upcomingEvents as $event)
                        <div class="p-3">
                            <div class="flex items-center gap-3">
                                <div class="w-1 h-10 rounded-full" style="background-color: {{ $event->typeColor }}"></div>
                                <div class="flex-1 min-w-0">
                                    <flux:heading level="4" class="truncate">{{ $event->title }}</flux:heading>
                                    <flux:text size="sm">{{ $event->starts_at->format('D, M j') }} at {{ $event->starts_at->format('H:i') }}</flux:text>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center">
                            <flux:text size="sm">No upcoming events</flux:text>
                        </div>
                    @endforelse
                </div>
            </flux:card>

            {{-- My Progress (Gamification) --}}
            <flux:card class="overflow-hidden">
                <a href="{{ route('gamification') }}" wire:navigate class="block">
                    <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-amber-500/10 to-orange-600/10 dark:from-amber-500/20 dark:to-orange-600/20">
                        <flux:heading level="3" class="flex items-center gap-2">
                            <span>🎯</span> My Progress
                        </flux:heading>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">🔥</span>
                                <div>
                                    <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ auth()->user()->current_streak ?? 0 }}</p>
                                    <flux:text size="xs" variant="subtle">day streak</flux:text>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xl">⭐</span>
                                <div>
                                    <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ number_format(auth()->user()->points ?? 0) }}</p>
                                    <flux:text size="xs" variant="subtle">points</flux:text>
                                </div>
                            </div>
                        </div>
                        @php $badges = auth()->user()->badges()->latest('badge_user.earned_at')->limit(3)->get(); @endphp
                        @if($badges->isNotEmpty())
                            <div class="flex items-center gap-1 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                <flux:text size="xs" variant="subtle" class="mr-2">Recent badges:</flux:text>
                                @foreach($badges as $badge)
                                    <flux:tooltip :content="$badge->name">
                                        <span class="text-lg">{{ $badge->icon }}</span>
                                    </flux:tooltip>
                                @endforeach
                            </div>
                        @endif
                        <flux:text size="xs" variant="subtle" class="mt-2 block text-amber-600 dark:text-amber-400">View achievements →</flux:text>
                    </div>
                </a>
            </flux:card>

            {{-- Quick Stats --}}
            <flux:card>
                <flux:heading level="3" class="mb-3">This Week</flux:heading>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ \App\Models\Standup::where('date', '>=', now()->startOfWeek())->count() }}
                        </p>
                        <flux:text size="xs">Check-ins</flux:text>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ \App\Models\Event::where('starts_at', '>=', now()->startOfWeek())->where('starts_at', '<=', now()->endOfWeek())->count() }}
                        </p>
                        <flux:text size="xs">Huddles</flux:text>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>
