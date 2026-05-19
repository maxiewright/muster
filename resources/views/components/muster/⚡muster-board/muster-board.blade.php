
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading level="1">Muster Board</flux:heading>
            <flux:subheading>Review team musters and daily progress reports</flux:subheading>
        </div>

        @if($selectedDate->isToday())
            <flux:button variant="primary" icon="check" href="{{ $this->myMuster ? route('muster.edit', $this->myMuster) : route('muster.create') }}" wire:navigate>
                {{ $this->myMuster ? 'Edit Muster' : 'Start Muster' }}
            </flux:button>
        @endif
    </div>

    {{-- Date Navigation --}}
    <flux:card class="p-4">
        <div class="flex items-center justify-between">
            <flux:button icon="chevron-left" wire:click="previousDay" variant="ghost" />

            <div class="text-center">
                <flux:button variant="ghost" wire:click="goToToday" class="text-lg font-semibold hover:text-blue-600">
                    {{ $selectedDate->format('l, F j, Y') }}
                </flux:button>
                @if($selectedDate->isToday())
                    <flux:badge color="green" size="sm" class="ml-2">Today</flux:badge>
                @elseif($selectedDate->isYesterday())
                    <flux:badge color="zinc" size="sm" class="ml-2">Yesterday</flux:badge>
                @endif
            </div>

            <flux:button icon="chevron-right" wire:click="nextDay" :disabled="$selectedDate->isToday()" variant="ghost" />
        </div>
    </flux:card>

    {{-- Stats Bar --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        {{-- Check-ins --}}
        <flux:card class="p-4 flex items-center gap-3">
            <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                <flux:icon name="circle-check" class="text-green-600 dark:text-green-400" />
            </div>
            <div>
                <flux:heading level="2">{{ $this->stats['total_checkins'] }}/{{ $this->stats['total_team'] }}</flux:heading>
                <flux:text size="xs" variant="subtle">Checked In</flux:text>
            </div>
        </flux:card>

        {{-- Tasks Planned --}}
        <flux:card class="p-4 flex items-center gap-3">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <span class="text-xl">🎯</span>
            </div>
            <div>
                <flux:heading level="2">{{ $this->stats['tasks_planned'] }}</flux:heading>
                <flux:text size="xs" variant="subtle">Tasks Planned</flux:text>
            </div>
        </flux:card>

        {{-- Tasks Completed --}}
        <flux:card class="p-4 flex items-center gap-3">
            <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                <span class="text-xl">✅</span>
            </div>
            <div>
                <flux:heading level="2">{{ $this->stats['tasks_completed'] }}</flux:heading>
                <flux:text size="xs" variant="subtle">Completed</flux:text>
            </div>
        </flux:card>

        {{-- Blockers --}}
        <flux:card class="p-4 flex items-center gap-3">
            <div class="p-2 {{ $this->stats['blockers'] > 0 ? 'bg-red-100 dark:bg-red-900/30' : 'bg-zinc-100 dark:bg-zinc-700' }} rounded-lg">
                <span class="text-xl">🚧</span>
            </div>
            <div>
                <flux:heading level="2" class="{{ $this->stats['blockers'] > 0 ? '!text-red-600 dark:!text-red-400' : '' }}">
                    {{ $this->stats['blockers'] }}
                </flux:heading>
                <flux:text size="xs" variant="subtle">Blockers</flux:text>
            </div>
        </flux:card>
    </div>

    {{-- Team Mood Summary --}}
    @if($this->stats['moods']->isNotEmpty())
        <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
            <span>Team Mood:</span>
            <div class="flex items-center gap-1">
                @foreach($this->stats['moods'] as $mood => $count)
                    @php $moodEnum = \App\Enums\Mood::tryFrom($mood) @endphp
                    @if($moodEnum)
                        <flux:badge size="sm" color="zinc" variant="pill" title="{{ $moodEnum->label() }}">
                            <span class="mr-1">{{ $moodEnum->emoji() }}</span> {{ $count }}
                        </flux:badge>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- Musters List --}}
    <div class="space-y-4">
        @forelse($this->musters as $muster)
            <flux:card class="!p-0 overflow-hidden {{ $muster->user_id === auth()->id() ? 'ring-2 ring-blue-500/50' : '' }}">
                {{-- Muster Header --}}
                <button wire:click="toggleExpand({{ $muster->id }})"
                        class="w-full p-4 text-left hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition bg-transparent border-0 cursor-pointer">
                    <div class="flex items-start gap-4">
                        {{-- Avatar --}}
                        <flux:avatar :name="$muster->user->name" variant="solid" size="lg" />

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <flux:heading level="4">{{ $muster->user->name }}</flux:heading>
                                @if($muster->user_id === auth()->id())
                                    <flux:badge size="xs" color="blue">You</flux:badge>
                                @endif
                                @if($muster->mood)
                                    <span class="text-xl" title="{{ $muster->mood->label() }}">
                                        {{ $muster->mood->emoji() }}
                                    </span>
                                @endif
                                <flux:text size="xs" variant="subtle">
                                    {{ $muster->created_at->format('H:i') }}
                                </flux:text>
                            </div>

                            {{-- Task Summary --}}
                            <div class="flex flex-wrap items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                                @php
                                    $planned = $muster->tasks->where('pivot.status', \App\Enums\MusterTaskStatus::Planned->value)->count();
                                    $completed = $muster->tasks->where('pivot.status', \App\Enums\MusterTaskStatus::Completed->value)->count();
                                    $workedOn = $muster->tasks->where('pivot.status', \App\Enums\MusterTaskStatus::Ongoing->value)->count();
                                    $carriedOver = $muster->tasks->where('pivot.status', \App\Enums\MusterTaskStatus::CarriedOver->value)->count();
                                @endphp

                                @if($planned > 0)
                                    <span class="inline-flex items-center gap-1"><span>🎯</span> {{ $planned }} planned</span>
                                @endif
                                @if($workedOn > 0)
                                    <span class="inline-flex items-center gap-1"><span>🔨</span> {{ $workedOn }} in progress</span>
                                @endif
                                @if($completed > 0)
                                    <span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400"><span>✅</span> {{ $completed }} done</span>
                                @endif
                                @if($carriedOver > 0)
                                    <span class="inline-flex items-center gap-1 text-orange-600 dark:text-orange-400"><span>➡️</span> {{ $carriedOver }} carried over</span>
                                @endif
                            </div>

                            {{-- Blocker Warning --}}
                            @if($muster->blockers)
                                <div class="mt-2 flex items-start gap-2 text-sm text-red-600 dark:text-red-400">
                                    <span class="flex-shrink-0">⚠️</span>
                                    <span class="line-clamp-1">{{ $muster->blockers }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Expand Icon --}}
                        <flux:icon name="chevron-down" class="text-zinc-400 transition-transform {{ $expandedMusterId === $muster->id ? 'rotate-180' : '' }}" />
                    </div>
                </button>

                {{-- Expanded Content --}}
                @if($expandedMusterId === $muster->id)
                    <div class="border-t border-zinc-200 dark:border-zinc-700 p-4 bg-zinc-50 dark:bg-zinc-900/50">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Tasks --}}
                            <div class="space-y-4">
                                <flux:heading level="4">Tasks</flux:heading>

                                @if($muster->tasks->isEmpty())
                                    <flux:text size="sm" variant="subtle" class="italic">No tasks recorded</flux:text>
                                @else
                                    <div class="space-y-3">
                                        @foreach($muster->tasks->groupBy('pivot.status') as $status => $tasks)
                                            @php $statusEnum = \App\Enums\MusterTaskStatus::tryFrom($status) @endphp
                                            <div>
                                                <div class="flex items-center gap-2 mb-2">
                                                    <flux:icon :name="$statusEnum?->icon() ?? 'clipboard'" class="size-4" />
                                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $statusEnum?->label() }}</span>
                                                    <span class="text-xs text-zinc-500">({{ $tasks->count() }})</span>
                                                </div>
                                                <div class="space-y-2 pl-6">
                                                    @foreach($tasks as $task)
                                                        <div class="flex items-start gap-2 text-sm">
                                                            <flux:badge size="xs" :color="$task->status->color()">{{ $task->status->label() }}</flux:badge>
                                                            <span class="text-zinc-700 dark:text-zinc-300">{{ $task->title }}</span>
                                                        </div>
                                                        @if($task->pivot->notes)
                                                            @php $notes = json_decode($task->pivot->notes, true) @endphp
                                                            @if($notes)
                                                                <div class="ml-6 text-xs text-zinc-500 dark:text-zinc-400 italic">
                                                                    @foreach($notes as $note)
                                                                        <p>• {{ $note }}</p>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Blockers & Details --}}
                            <div class="space-y-4">
                                @if($muster->blockers)
                                    <div>
                                        <flux:heading level="4" class="mb-2 flex items-center gap-2"><span>🚧</span> Blockers</flux:heading>
                                        <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                            <p class="text-sm text-red-700 dark:text-red-300">{{ $muster->blockers }}</p>
                                        </div>
                                    </div>
                                @endif

                                {{-- User Stats --}}
                                <div>
                                    <flux:heading level="4" class="mb-2">Stats</flux:heading>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-700 rounded">
                                            <span class="text-zinc-500 dark:text-zinc-400">Streak:</span>
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100 ml-1">🔥 {{ $muster->user->current_streak ?? 0 }} days</span>
                                        </div>
                                        <div class="p-2 bg-zinc-100 dark:bg-zinc-700 rounded">
                                            <span class="text-zinc-500 dark:text-zinc-400">Points:</span>
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100 ml-1">{{ number_format($muster->user->points ?? 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        @if($muster->user_id === auth()->id() && $selectedDate->isToday())
                            <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end">
                                <flux:link href="{{ route('muster.edit', $muster) }}" wire:navigate>Edit Muster →</flux:link>
                            </div>
                        @endif
                    </div>
                @endif
            </flux:card>
        @empty
            <flux:card class="p-12 text-center">
                <div class="text-5xl mb-4">🏕️</div>
                <flux:heading level="3" class="mb-2">No check-ins {{ $selectedDate->isToday() ? 'yet' : 'on this day' }}</flux:heading>
                <flux:text variant="subtle" class="mb-4">
                    @if($selectedDate->isToday())
                        Be the first to report for muster!
                    @else
                        No one checked in on {{ $selectedDate->format('F j, Y') }}
                    @endif
                </flux:text>
                @if($selectedDate->isToday() && !$this->myMuster)
                    <flux:button variant="primary" href="{{ route('muster.create') }}" wire:navigate>Start Muster</flux:button>
                @endif
            </flux:card>
        @endforelse
    </div>

    {{-- Who hasn't checked in --}}
    @if($selectedDate->isToday())
        @php
            $notCheckedIn = $this->teamMembers->whereNotIn('id', $this->checkedInUsers);
        @endphp
        @if($notCheckedIn->isNotEmpty())
            <flux:card class="!bg-amber-50 dark:!bg-amber-900/20 !border-amber-200 dark:!border-amber-800 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-amber-600 dark:text-amber-400">⏳</span>
                    <span class="font-medium text-amber-800 dark:text-amber-200">Awaiting Check-in</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($notCheckedIn as $user)
                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 rounded-full text-sm text-amber-700 dark:text-amber-300">
                            <flux:avatar :name="$user->name" size="xs" />
                            {{ $user->name }}
                            @if($user->id === auth()->id()) <span class="text-xs">(you)</span> @endif
                        </span>
                    @endforeach
                </div>
            </flux:card>
        @endif
    @endif
</div>
