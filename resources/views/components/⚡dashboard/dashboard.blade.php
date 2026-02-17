<div class="space-y-6">
    <flux:card class="relative overflow-hidden border-zinc-200/70 bg-gradient-to-br from-zinc-950 via-zinc-900 to-zinc-800 text-zinc-100 dark:border-zinc-700">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.28),transparent_45%)]"></div>
        <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(120deg,transparent_0%,rgba(245,158,11,0.12)_38%,transparent_76%)]"></div>

        <div class="relative grid gap-6 lg:grid-cols-3">
            <div class="space-y-3 lg:col-span-2">
                <flux:badge color="emerald" size="sm" icon="shield-check">Operational</flux:badge>
                <flux:heading size="xl" level="1" class="!text-zinc-50">Mission Control</flux:heading>
                <flux:text class="max-w-2xl !text-zinc-300">
                    Coordinate your squad, clear blockers fast, and stack points with consistent execution.
                    {{ now()->format('l, F j, Y') }}
                </flux:text>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-zinc-700 bg-zinc-900/70 p-3">
                        <flux:text size="xs" class="!text-zinc-400">Streak</flux:text>
                        <flux:heading level="2" size="xl" class="!text-amber-300">{{ auth()->user()->current_streak ?? 0 }} days</flux:heading>
                    </div>
                    <div class="rounded-xl border border-zinc-700 bg-zinc-900/70 p-3">
                        <flux:text size="xs" class="!text-zinc-400">Points</flux:text>
                        <flux:heading level="2" size="xl" class="!text-emerald-300">{{ number_format(auth()->user()->points ?? 0) }}</flux:heading>
                    </div>
                    <div class="rounded-xl border border-zinc-700 bg-zinc-900/70 p-3">
                        <flux:text size="xs" class="!text-zinc-400">Today</flux:text>
                        <flux:heading level="2" size="xl" class="!text-sky-300">{{ $todaysStandups->count() }} check-ins</flux:heading>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                <flux:button href="{{ $myStandup ? route('standup.edit', $myStandup) : route('standup.create') }}" variant="primary" class="w-full min-h-[44px] !bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600" wire:navigate>
                    {{ $myStandup ? 'Update Check-In' : 'Check In' }}
                </flux:button>
                <flux:button href="{{ route('tasks') }}" variant="primary" class="w-full min-h-[44px] !bg-sky-600 hover:!bg-sky-700 !border-sky-600 !text-white" wire:navigate>
                    Open Task Board
                </flux:button>
                <flux:button href="{{ route('training.dashboard') }}" variant="primary" class="w-full min-h-[44px] !bg-amber-600 hover:!bg-amber-700 !border-amber-600 !text-white" wire:navigate>
                    Training Ops
                </flux:button>
            </div>
        </div>
    </flux:card>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <flux:card class="p-0 overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900/50">
                <flux:heading level="2" size="lg" class="flex items-center gap-2">
                    <flux:icon.bell class="size-4" />
                    Notifications
                </flux:heading>
                <flux:badge color="amber" size="sm">{{ $recentPartnerNotifications->whereNull('read_at')->count() }} unread</flux:badge>
            </div>

            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($recentPartnerNotifications as $notification)
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <flux:avatar :name="$notification->fromUser?->name ?? 'System'" variant="solid" />
                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex items-center gap-2">
                                    <flux:heading level="3" class="truncate">{{ $notification->title }}</flux:heading>
                                    @if(!$notification->read_at)
                                        <span class="inline-block size-2 rounded-full bg-amber-500"></span>
                                    @endif
                                </div>
                                <flux:text size="sm" class="!text-zinc-600 dark:!text-zinc-300">
                                    {{ $notification->message ?: 'New mission update from your accountability squad.' }}
                                </flux:text>
                                <flux:text size="xs" class="mt-1 !text-zinc-500">
                                    {{ $notification->fromUser?->name ?? 'System' }} · {{ $notification->created_at->diffForHumans() }}
                                </flux:text>

                                @if($notification->type === 'partner_request' && $notification->goal && !$notification->actioned_at)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <flux:button size="xs" variant="primary" class="!bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600" wire:click="acceptPartnerRequest({{ $notification->id }})">
                                            Accept
                                        </flux:button>
                                        <flux:button size="xs" variant="ghost" class="!text-red-300 hover:!text-red-200" wire:click="declinePartnerRequest({{ $notification->id }})">
                                            Decline
                                        </flux:button>
                                        <flux:button size="xs" variant="subtle" href="{{ route('training.goals.show', $notification->goal->slug) }}" wire:navigate>
                                            Review
                                        </flux:button>
                                    </div>
                                @elseif($notification->goal)
                                    <div class="mt-3">
                                        <flux:button size="xs" variant="subtle" href="{{ route('training.goals.show', $notification->goal->slug) }}" wire:navigate>
                                            View
                                        </flux:button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center">
                        <flux:text variant="subtle">No notifications yet. Your next squad update will appear here.</flux:text>
                    </div>
                @endforelse
            </div>
        </flux:card>

        <flux:card class="p-0 overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900/50">
                <flux:heading level="2" size="lg" class="flex items-center gap-2">
                    <flux:icon.messages-square class="size-4" />
                    Team Updates
                </flux:heading>
                <flux:link href="{{ route('standups') }}" wire:navigate>View all</flux:link>
            </div>

            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($teamUpdates as $update)
                    <div class="p-4">
                        <div class="mb-2 flex items-center gap-2">
                            <flux:avatar :name="$update->user->name" variant="solid" />
                            <flux:heading level="3">{{ $update->user->name }}</flux:heading>
                            <flux:text size="xs" class="!text-zinc-500">{{ $update->created_at->diffForHumans() }}</flux:text>
                        </div>
                        <flux:text size="sm" class="!text-zinc-600 dark:!text-zinc-300">
                            {{ $update->blockers ? 'Blocker: '.$update->blockers : 'Status posted with '.$update->standupTasks->count().' linked tasks.' }}
                        </flux:text>
                    </div>
                @empty
                    <div class="p-6 text-center">
                        <flux:text variant="subtle">No team updates yet today.</flux:text>
                    </div>
                @endforelse
            </div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <flux:card class="p-0 overflow-hidden">
                <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900/50">
                    <flux:heading level="2" size="lg">Today's Muster</flux:heading>
                </div>

                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($todaysStandups as $standup)
                        <div class="p-4">
                            <div class="flex items-start gap-3">
                                <flux:avatar :name="$standup->user->name" variant="solid" />
                                <div class="min-w-0 flex-1">
                                    <div class="mb-2 flex items-center gap-2">
                                        <flux:heading level="3">{{ $standup->user->name }}</flux:heading>
                                        @if($standup->mood)
                                            <flux:tooltip :content="$standup->mood->label()">
                                                <span class="cursor-help text-lg">{{ $standup->mood->emoji() }}</span>
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
                                                    <span class="text-red-500">!</span>
                                                    <flux:text class="!text-red-600 dark:!text-red-400">
                                                        {{ $blocked->take(3)->map(fn($s) => optional($s->task)->title)->filter()->implode(', ') }}
                                                        @if($blocked->count() > 3)
                                                            +{{ $blocked->count() - 3 }} more
                                                        @endif
                                                    </flux:text>
                                                </div>
                                            @endif

                                            @if($completed->isEmpty() && $planned->isEmpty() && $blocked->isEmpty())
                                                <flux:text variant="subtle" class="italic">No tasks linked to this standup.</flux:text>
                                            @endif
                                        </div>
                                    @endif

                                    @if($standup->blockers)
                                        <div class="mt-2 flex items-start gap-1">
                                            <span class="text-red-500">!</span>
                                            <flux:text class="!text-red-600 dark:!text-red-400">{{ $standup->blockers }}</flux:text>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <flux:heading level="3">No one has checked in yet today.</flux:heading>
                            <flux:text>Be first to post the daily update.</flux:text>
                        </div>
                    @endforelse
                </div>
            </flux:card>
        </div>

        <div class="space-y-4">
            <flux:card class="p-0 overflow-hidden">
                <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900/50">
                    <flux:heading level="2" size="lg">Upcoming Huddles</flux:heading>
                    <flux:link href="{{ route('calendar') }}" wire:navigate>View all</flux:link>
                </div>

                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($upcomingEvents as $event)
                        <div class="p-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-1 rounded-full" style="background-color: {{ $event->typeColor }}"></div>
                                <div class="min-w-0 flex-1">
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

            <flux:card>
                <flux:heading level="3" class="mb-3">This Week</flux:heading>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $weeklyStandupsCount }}</p>
                        <flux:text size="xs">Check-ins</flux:text>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $weeklyEventsCount }}</p>
                        <flux:text size="xs">Huddles</flux:text>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>
