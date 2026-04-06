<div class="space-y-6 p-4 sm:p-6 lg:p-8" x-data="{ hoveredCard: null }">
    {{-- HUD Mission Control Header --}}
    <div class="glass-card tactical-glow relative overflow-hidden p-5 sm:p-6">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.28),transparent_45%)]"></div>
        <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(120deg,transparent_0%,rgba(245,158,11,0.12)_38%,transparent_76%)]"></div>

        <div class="relative grid gap-6 lg:grid-cols-3">
            <div class="space-y-3 lg:col-span-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full border border-emerald-600/20 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-400">
                        <span class="mr-1.5 inline-block h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                        Operational
                    </span>
                </div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-zinc-50 sm:text-3xl">Mission Control</h1>
                <p class="max-w-2xl text-sm text-slate-500 dark:text-zinc-400">
                    Coordinate your squad, clear blockers fast, and stack points with consistent execution.
                    {{ now()->format('l, F j, Y') }}
                </p>

                {{-- HUD Stats Grid --}}
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4" x-data="{ animatedStreak: 0, animatedPoints: 0, animatedCheckins: 0, animatedTasks: 0 }" x-init="
                    setTimeout(() => { let s = {{ auth()->user()->current_streak ?? 0 }}; let i = 0; let t = setInterval(() => { animatedStreak = ++i; if(i>=s) clearInterval(t); }, 40); }, 100);
                    setTimeout(() => { let s = {{ auth()->user()->points ?? 0 }}; let i = 0; let step = Math.max(1, Math.floor(s/30)); let t = setInterval(() => { i = Math.min(i+step, s); animatedPoints = i; if(i>=s) clearInterval(t); }, 30); }, 200);
                    setTimeout(() => { animatedCheckins = {{ $todaysStandups->count() }}; }, 300);
                    setTimeout(() => { animatedTasks = {{ $activeTasks->count() }}; }, 400);
                ">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-zinc-700/50 dark:bg-zinc-900/60">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400 dark:text-zinc-500">Streak</p>
                        <p class="text-xl font-bold text-amber-600 dark:text-amber-300" x-text="animatedStreak + ' days'"></p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-zinc-700/50 dark:bg-zinc-900/60">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400 dark:text-zinc-500">Points</p>
                        <p class="text-xl font-bold text-emerald-600 dark:text-emerald-300" x-text="animatedPoints.toLocaleString()"></p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-zinc-700/50 dark:bg-zinc-900/60">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400 dark:text-zinc-500">Today's Ops</p>
                        <p class="text-xl font-bold text-sky-600 dark:text-sky-300" x-text="animatedCheckins + ' check-ins'"></p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-zinc-700/50 dark:bg-zinc-900/60">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400 dark:text-zinc-500">Active Missions</p>
                        <p class="text-xl font-bold text-violet-600 dark:text-violet-300" x-text="animatedTasks + ' tasks'"></p>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                <a href="{{ $myStandup ? route('standup.edit', $myStandup) : route('standup.create') }}" wire:navigate
                   class="flex min-h-[44px] items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500 active:scale-[0.98]">
                    <flux:icon.shield-check class="size-4" />
                    {{ $myStandup ? 'Update Check-In' : 'Check In' }}
                </a>
                <a href="{{ route('tasks') }}" wire:navigate
                   class="flex min-h-[44px] items-center justify-center gap-2 rounded-lg bg-sky-600/80 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-500/80 active:scale-[0.98]">
                    <flux:icon.clipboard-list class="size-4" />
                    Open Task Board
                </a>
                <a href="{{ route('training.dashboard') }}" wire:navigate
                   class="flex min-h-[44px] items-center justify-center gap-2 rounded-lg border border-slate-300 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-200 dark:border-zinc-700 dark:bg-zinc-800/80 dark:text-zinc-200 dark:hover:border-zinc-600 dark:hover:bg-zinc-700/80 active:scale-[0.98]">
                    <flux:icon.graduation-cap class="size-4" />
                    Training Ops
                </a>
            </div>
        </div>
    </div>

    {{-- Primary Objectives (Active Tasks) --}}
    @if($activeTasks->isNotEmpty())
        <div class="glass-card tactical-glow overflow-hidden p-0">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-white/5">
                <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-600 dark:text-zinc-300">
                    <flux:icon.shield-check class="size-4 text-emerald-600 dark:text-emerald-400" />
                    Primary Objectives
                </h2>
                <a href="{{ route('tasks') }}" wire:navigate class="text-xs text-slate-500 transition hover:text-emerald-600 dark:text-zinc-500 dark:hover:text-emerald-400">View all →</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-white/5">
                @foreach($activeTasks as $task)
                    <div class="flex items-center gap-3 px-4 py-3 transition hover:bg-slate-50 dark:hover:bg-white/[0.02]"
                         x-on:mouseenter="hoveredCard = 'task-{{ $task->id }}'"
                         x-on:mouseleave="hoveredCard = null">
                        {{-- Priority indicator --}}
                        @php
                            $priorityColor = match($task->priority) {
                                \App\Enums\TaskPriority::Urgent => 'bg-red-500 animate-pulse',
                                \App\Enums\TaskPriority::High => 'bg-orange-500',
                                \App\Enums\TaskPriority::Medium => 'bg-blue-500',
                                \App\Enums\TaskPriority::Low => 'bg-emerald-500',
                                default => 'bg-zinc-500',
                            };
                            $priorityLabel = match($task->priority) {
                                \App\Enums\TaskPriority::Urgent => 'ALPHA',
                                \App\Enums\TaskPriority::High => 'BRAVO',
                                \App\Enums\TaskPriority::Medium => 'CHARLIE',
                                \App\Enums\TaskPriority::Low => 'DELTA',
                                default => 'ECHO',
                            };
                        @endphp
                        <div class="flex flex-col items-center gap-0.5">
                            <span class="h-6 w-1 rounded-full {{ $priorityColor }}"></span>
                            <span class="text-[8px] font-bold uppercase tracking-widest text-slate-400 dark:text-zinc-600">{{ $priorityLabel }}</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800 dark:text-zinc-200">{{ $task->title }}</p>
                            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-zinc-500">
                                <span>{{ $task->status->label() }}</span>
                                @if($task->due_date)
                                    <span>·</span>
                                    <span class="{{ $task->isOverdue() ? 'text-red-400' : ($task->isDueToday() ? 'text-amber-400' : '') }}">
                                        {{ $task->due_date->format('M j') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($task->assignee)
                            <flux:avatar :name="$task->assignee->name" size="xs" />
                        @endif

                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-medium
                            {{ match($task->status) {
                                \App\Enums\TaskStatus::InProgress => 'border-amber-600/20 text-amber-700 dark:border-amber-500/30 dark:text-amber-400',
                                \App\Enums\TaskStatus::Review => 'border-violet-600/20 text-violet-700 dark:border-violet-500/30 dark:text-violet-400',
                                \App\Enums\TaskStatus::Todo => 'border-sky-600/20 text-sky-700 dark:border-sky-500/30 dark:text-sky-400',
                                \App\Enums\TaskStatus::Blocked => 'border-red-600/20 text-red-700 dark:border-red-500/30 dark:text-red-400',
                                default => 'border-slate-300 text-slate-500 dark:border-zinc-600/30 dark:text-zinc-500',
                            } }}">
                            {{ $task->status->label() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Secondary Intel: Notifications + Team Updates --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        {{-- Intel Feed (Notifications) --}}
        <div class="glass-card tactical-glow overflow-hidden p-0">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-white/5">
                <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-600 dark:text-zinc-300">
                    <flux:icon.bell class="size-4 text-amber-500 dark:text-amber-400" />
                    Intel Feed
                </h2>
                <span class="inline-flex items-center rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                    {{ $recentPartnerNotifications->whereNull('read_at')->count() }} unread
                </span>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse($recentPartnerNotifications as $notification)
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <flux:avatar :name="$notification->fromUser?->name ?? 'System'" variant="solid" />
                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex items-center gap-2">
                                    <p class="truncate text-sm font-medium text-slate-800 dark:text-zinc-200">{{ $notification->title }}</p>
                                    @if(!$notification->read_at)
                                        <span class="inline-block size-2 rounded-full bg-amber-500"></span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 dark:text-zinc-400">
                                    {{ $notification->message ?: 'New mission update from your accountability squad.' }}
                                </p>
                                <p class="mt-1 text-[10px] text-slate-400 dark:text-zinc-600">
                                    {{ $notification->fromUser?->name ?? 'System' }} · {{ $notification->created_at->diffForHumans() }}
                                </p>

                                @if($notification->type === 'partner_request' && $notification->goal && !$notification->actioned_at)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <button wire:click="acceptPartnerRequest({{ $notification->id }})" class="rounded-md bg-emerald-600 px-3 py-1 text-xs font-medium text-white transition hover:bg-emerald-500">
                                            Accept
                                        </button>
                                        <button wire:click="declinePartnerRequest({{ $notification->id }})" class="rounded-md px-3 py-1 text-xs font-medium text-red-600 transition hover:text-red-500 dark:text-red-400 dark:hover:text-red-300">
                                            Decline
                                        </button>
                                        <a href="{{ route('training.goals.show', $notification->goal->slug) }}" wire:navigate class="rounded-md border border-slate-300 px-3 py-1 text-xs font-medium text-slate-500 transition hover:text-slate-700 dark:border-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300">
                                            Review
                                        </a>
                                    </div>
                                @elseif($notification->goal)
                                    <div class="mt-3">
                                        <a href="{{ route('training.goals.show', $notification->goal->slug) }}" wire:navigate class="text-xs text-slate-500 transition hover:text-emerald-600 dark:text-zinc-500 dark:hover:text-emerald-400">
                                            View →
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center">
                        <p class="text-sm text-slate-400 dark:text-zinc-500">No intel yet. Your next squad update will appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Squad Comms (Team Updates) --}}
        <div class="glass-card tactical-glow overflow-hidden p-0">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-white/5">
                <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-600 dark:text-zinc-300">
                    <flux:icon.messages-square class="size-4 text-sky-600 dark:text-sky-400" />
                    Squad Comms
                </h2>
                <a href="{{ route('standups') }}" wire:navigate class="text-xs text-slate-500 transition hover:text-emerald-600 dark:text-zinc-500 dark:hover:text-emerald-400">View all →</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse($teamUpdates as $update)
                    <div class="p-4">
                        <div class="mb-2 flex items-center gap-2">
                            <flux:avatar :name="$update->user->name" variant="solid" />
                            <p class="text-sm font-medium text-slate-800 dark:text-zinc-200">{{ $update->user->name }}</p>
                            <span class="text-[10px] text-slate-400 dark:text-zinc-600">{{ $update->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-zinc-400">
                            {{ $update->blockers ? 'Blocker: '.$update->blockers : 'Status posted with '.$update->standupTasks->count().' linked tasks.' }}
                        </p>
                    </div>
                @empty
                    <div class="p-6 text-center">
                        <p class="text-sm text-slate-400 dark:text-zinc-500">No team updates yet today.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Today's Muster + Upcoming Operations + Weekly Summary --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="glass-card tactical-glow overflow-hidden p-0">
                <div class="border-b border-slate-200 px-4 py-3 dark:border-white/5">
                    <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-600 dark:text-zinc-300">
                        <flux:icon.person-standing class="size-4 text-emerald-600 dark:text-emerald-400" />
                        Today's Muster
                    </h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($todaysStandups as $standup)
                        <div class="p-4">
                            <div class="flex items-start gap-3">
                                <flux:avatar :name="$standup->user->name" variant="solid" />
                                <div class="min-w-0 flex-1">
                                    <div class="mb-2 flex items-center gap-2">
                                        <p class="text-sm font-medium text-slate-800 dark:text-zinc-200">{{ $standup->user->name }}</p>
                                        @if($standup->mood)
                                            <flux:tooltip :content="$standup->mood->label()">
                                                <span class="cursor-help text-lg">{{ $standup->mood->emoji() }}</span>
                                            </flux:tooltip>
                                        @endif
                                        <span class="text-[10px] text-slate-400 dark:text-zinc-600">{{ $standup->created_at->format('H:i') }}</span>
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
                                                    <span class="text-[10px] uppercase tracking-wider text-slate-400 dark:text-zinc-600">Completed:</span>
                                                    <p class="text-xs text-slate-500 dark:text-zinc-400">
                                                        {{ $completed->take(3)->map(fn($s) => optional($s->task)->title)->filter()->implode(', ') }}
                                                        @if($completed->count() > 3)
                                                            +{{ $completed->count() - 3 }} more
                                                        @endif
                                                    </p>
                                                </div>
                                            @endif

                                            @if($planned->isNotEmpty())
                                                <div>
                                                    <span class="text-[10px] uppercase tracking-wider text-slate-400 dark:text-zinc-600">Planned:</span>
                                                    <p class="text-xs text-slate-500 dark:text-zinc-400">
                                                        {{ $planned->take(3)->map(fn($s) => optional($s->task)->title)->filter()->implode(', ') }}
                                                        @if($planned->count() > 3)
                                                            +{{ $planned->count() - 3 }} more
                                                        @endif
                                                    </p>
                                                </div>
                                            @endif

                                            @if($blocked->isNotEmpty())
                                                <div class="flex items-start gap-1">
                                                    <span class="text-red-500">!</span>
                                                    <p class="text-xs text-red-400">
                                                        {{ $blocked->take(3)->map(fn($s) => optional($s->task)->title)->filter()->implode(', ') }}
                                                        @if($blocked->count() > 3)
                                                            +{{ $blocked->count() - 3 }} more
                                                        @endif
                                                    </p>
                                                </div>
                                            @endif

                                            @if($completed->isEmpty() && $planned->isEmpty() && $blocked->isEmpty())
                                                <p class="text-xs italic text-slate-400 dark:text-zinc-600">No tasks linked to this standup.</p>
                                            @endif
                                        </div>
                                    @endif

                                    @if($standup->blockers)
                                        <div class="mt-2 flex items-start gap-1">
                                            <span class="text-red-500">!</span>
                                            <p class="text-xs text-red-400">{{ $standup->blockers }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <h3 class="text-sm font-medium text-slate-600 dark:text-zinc-300">No one has checked in yet today.</h3>
                            <p class="mt-1 text-xs text-slate-400 dark:text-zinc-500">Be first to post the daily update.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-4">
            {{-- Upcoming Operations --}}
            <div class="glass-card tactical-glow overflow-hidden p-0">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-white/5">
                    <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-600 dark:text-zinc-300">
                        <flux:icon.calendar class="size-4 text-violet-600 dark:text-violet-400" />
                        Upcoming Ops
                    </h2>
                    <a href="{{ route('calendar') }}" wire:navigate class="text-xs text-slate-500 transition hover:text-emerald-600 dark:text-zinc-500 dark:hover:text-emerald-400">View all →</a>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($upcomingEvents as $event)
                        <div class="p-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-1 rounded-full" style="background-color: {{ $event->typeColor }}"></div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-slate-800 dark:text-zinc-200">{{ $event->title }}</p>
                                    <p class="text-[10px] text-slate-400 dark:text-zinc-500">{{ $event->starts_at->format('D, M j') }} at {{ $event->starts_at->format('H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center">
                            <p class="text-xs text-slate-400 dark:text-zinc-500">No upcoming events</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Weekly Summary --}}
            <div class="glass-card p-4">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-600 dark:text-zinc-300">This Week</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-sky-600 dark:text-sky-400">{{ $weeklyStandupsCount }}</p>
                        <p class="text-[10px] uppercase tracking-wider text-slate-400 dark:text-zinc-500">Check-ins</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $weeklyEventsCount }}</p>
                        <p class="text-[10px] uppercase tracking-wider text-slate-400 dark:text-zinc-500">Huddles</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
