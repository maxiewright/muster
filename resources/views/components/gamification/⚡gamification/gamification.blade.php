<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">Achievements</flux:heading>
            <flux:subheading>Your progress, badges, and leaderboard standing</flux:subheading>
        </div>
    </div>

    {{-- Stats Overview --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <flux:card class="p-4 flex items-center gap-3 bg-gradient-to-br from-amber-500/10 to-orange-600/10 dark:from-amber-500/20 dark:to-orange-600/20 border-amber-500/20">
            <div class="p-2 bg-amber-100 dark:bg-amber-900/40 rounded-lg">
                <span class="text-2xl" aria-hidden="true">⭐</span>
            </div>
            <div>
                <flux:heading level="2" class="text-amber-700 dark:text-amber-300">{{ number_format($this->user->points) }}</flux:heading>
                <flux:text size="xs" variant="subtle">Total Points</flux:text>
            </div>
        </flux:card>

        <flux:card class="p-4 flex items-center gap-3 bg-gradient-to-br from-orange-500/10 to-red-600/10 dark:from-orange-500/20 dark:to-red-600/20 border-orange-500/20">
            <div class="p-2 bg-orange-100 dark:bg-orange-900/40 rounded-lg">
                <span class="text-2xl" aria-hidden="true">🔥</span>
            </div>
            <div>
                <flux:heading level="2" class="text-orange-700 dark:text-orange-300">{{ $this->user->current_streak ?? 0 }}</flux:heading>
                <flux:text size="xs" variant="subtle">Current Streak</flux:text>
            </div>
        </flux:card>

        <flux:card class="p-4 flex items-center gap-3 bg-gradient-to-br from-violet-500/10 to-purple-600/10 dark:from-violet-500/20 dark:to-purple-600/20 border-violet-500/20">
            <div class="p-2 bg-violet-100 dark:bg-violet-900/40 rounded-lg">
                <span class="text-2xl" aria-hidden="true">🏆</span>
            </div>
            <div>
                <flux:heading level="2" class="text-violet-700 dark:text-violet-300">{{ $this->user->longest_streak ?? 0 }}</flux:heading>
                <flux:text size="xs" variant="subtle">Best Streak</flux:text>
            </div>
        </flux:card>

        <flux:card class="p-4 flex items-center gap-3 bg-gradient-to-br from-blue-500/10 to-indigo-600/10 dark:from-blue-500/20 dark:to-indigo-600/20 border-blue-500/20">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/40 rounded-lg">
                <span class="text-2xl" aria-hidden="true">🎖️</span>
            </div>
            <div>
                <flux:heading level="2" class="text-blue-700 dark:text-blue-300">#{{ $this->user->rank }}</flux:heading>
                <flux:text size="xs" variant="subtle">Leaderboard Rank</flux:text>
            </div>
        </flux:card>
    </div>

    {{-- Earned Badges - full width for better grid layout --}}
    <flux:card class="p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
            <flux:heading level="2" size="lg" class="flex items-center gap-2">
                <span>🏅</span> Your Badges
            </flux:heading>
        </div>
        <div class="p-4">
            @if($this->earnedBadges->isNotEmpty())
                <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-3">
                        @foreach($this->earnedBadges as $badge)
                            <flux:tooltip :content="$badge->description">
                                <div class="flex flex-col items-center gap-2 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
                                     style="border-left: 3px solid {{ $badge->color }};">
                                    <span class="text-3xl">{{ $badge->icon }}</span>
                                    <span class="text-sm font-medium text-center truncate w-full">{{ $badge->name }}</span>
                                    <span class="text-xs text-zinc-500">{{ \Illuminate\Support\Carbon::parse($badge->pivot->earned_at)->diffForHumans() }}</span>
                                </div>
                            </flux:tooltip>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <span class="text-4xl mb-2 block opacity-50">🎖️</span>
                        <flux:text variant="subtle">No badges yet. Check in daily to earn your first!</flux:text>
                        <flux:button href="{{ route('standup.create') }}" variant="primary" size="sm" class="mt-3" wire:navigate>
                            Check In Now
                        </flux:button>
                    </div>
                @endif
        </div>
    </flux:card>

    {{-- Leaderboard --}}
    <flux:card class="p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
            <flux:heading level="2" size="lg" class="flex items-center gap-2">
                <span>📊</span> Leaderboard
            </flux:heading>
        </div>
        <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach($this->leaderboard as $index => $player)
                    <div class="flex items-center gap-4 px-4 py-3 {{ $player->id === auth()->id() ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                        <div class="w-8 text-center font-bold text-zinc-500 dark:text-zinc-400">
                            {{ $index + 1 }}
                        </div>
                        <flux:avatar :name="$player->name" size="sm" variant="solid" />
                        <div class="flex-1 min-w-0">
                            <flux:heading level="4" class="truncate">
                                {{ $player->name }}
                                @if($player->id === auth()->id())
                                    <flux:badge size="xs" color="blue" class="ml-1">You</flux:badge>
                                @endif
                            </flux:heading>
                            <flux:text size="xs" variant="subtle">
                                {{ number_format($player->points) }} pts · 🔥 {{ $player->current_streak ?? 0 }} day streak
                            </flux:text>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ number_format($player->points) }}</span>
                        </div>
                    </div>
                @endforeach
        </div>
    </flux:card>

    {{-- Point History --}}
    <flux:card class="p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
            <flux:heading level="2" size="lg" class="flex items-center gap-2">
                <span>📜</span> Recent Activity
            </flux:heading>
        </div>
        <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
            @forelse($this->recentPointLogs as $log)
                <div class="flex items-center justify-between px-4 py-3">
                    <div>
                        <flux:text class="font-medium">{{ $log->reason }}</flux:text>
                        <flux:text size="xs" variant="subtle">{{ $log->created_at->diffForHumans() }}</flux:text>
                    </div>
                    <flux:badge color="green" size="sm">+{{ $log->points }}</flux:badge>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <flux:text variant="subtle">No points earned yet. Complete a check-in to get started!</flux:text>
                </div>
            @endforelse
        </div>
    </flux:card>

    {{-- All Badges (locked) --}}
    <flux:card class="p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
            <flux:heading level="2" size="lg" class="flex items-center gap-2">
                <span>🔒</span> All Badges
            </flux:heading>
            <flux:text size="sm" variant="subtle" class="mt-1 block">Unlock more by checking in and building streaks</flux:text>
        </div>
        <div class="p-4">
            @php
                $earnedIds = $this->earnedBadges->pluck('id')->toArray();
            @endphp
            <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-3">
                @foreach($this->allBadges as $badge)
                    @php $earned = in_array($badge->id, $earnedIds); @endphp
                    <flux:tooltip :content="$badge->description">
                        <div class="flex flex-col items-center gap-1.5 p-3 rounded-lg transition {{ $earned ? 'bg-zinc-100 dark:bg-zinc-800' : 'opacity-40 grayscale' }}"
                             style="{{ $earned ? "border-left: 3px solid {$badge->color};" : '' }}">
                            <span class="text-2xl">{{ $badge->icon }}</span>
                            <span class="text-xs font-medium text-center truncate w-full">{{ $badge->name }}</span>
                        </div>
                    </flux:tooltip>
                @endforeach
            </div>
        </div>
    </flux:card>
</div>
