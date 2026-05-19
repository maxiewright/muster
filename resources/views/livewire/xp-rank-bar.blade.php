<div wire:poll.30s="refreshXp">
    <a href="{{ route('gamification') }}" wire:navigate class="block">
        <div class="mx-3 mb-2 rounded-lg border border-emerald-600/15 bg-gradient-to-br from-emerald-500/8 to-slate-100/50 px-3 py-2.5 dark:border-emerald-500/20 dark:from-emerald-500/10 dark:to-zinc-900/50">
            {{-- Progress tier display --}}
            <div class="mb-1.5 flex items-center justify-between gap-2 text-sm">
                <div class="flex items-center gap-1.5 min-w-0">
                    <span class="text-base" aria-hidden="true">{{ $rankIcon }}</span>
                    <span class="font-semibold text-emerald-700 dark:text-emerald-300 truncate">{{ $rankLabel }}</span>
                </div>
                <span class="shrink-0 text-xs text-slate-500 dark:text-zinc-400">{{ number_format($points) }} XP</span>
            </div>

            {{-- XP progress bar --}}
            <div class="relative h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-zinc-800">
                <div
                    class="absolute inset-y-0 left-0 rounded-full bg-gradient-to-r from-emerald-500 to-emerald-400 transition-all duration-700 ease-out"
                    style="width: {{ $progressPercent }}%; animation: xp-fill 1s ease-out;"
                ></div>
            </div>

            {{-- Next tier hint --}}
            @if($nextRankLabel)
                <p class="mt-1 text-[10px] text-slate-400 dark:text-zinc-500 truncate">
                    {{ $nextRankPoints - $points }} XP to {{ $nextRankLabel }}
                </p>
            @else
                <p class="mt-1 text-[10px] text-amber-600/70 dark:text-amber-400/70">Top tier reached</p>
            @endif
        </div>
    </a>
</div>
