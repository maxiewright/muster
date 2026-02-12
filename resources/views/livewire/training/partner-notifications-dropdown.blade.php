<flux:dropdown align="end">
    <flux:button variant="ghost" size="sm" icon="bell" class="relative">
        @if($this->unreadCount > 0)
            <span class="absolute top-0 right-0 size-2 bg-red-500 rounded-full border-2 border-white dark:border-zinc-800"></span>
        @endif
    </flux:button>

    <flux:menu class="w-80">
        <div class="px-4 py-2 border-b border-zinc-100 dark:border-zinc-800 flex justify-between items-center">
            <span class="font-bold text-sm">{{ __('Notifications') }}</span>
            @if($this->unreadCount > 0)
                <flux:button size="xs" variant="ghost" wire:click="markAllAsRead">{{ __('Mark all read') }}</flux:button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse($this->notifications as $notification)
                @php
                    $targetUrl = $notification->goal
                        ? route('training.goals.show', $notification->goal->slug)
                        : route('training.dashboard');
                @endphp
                <flux:menu.item
                    class="p-4 border-b border-zinc-50 dark:border-zinc-900 last:border-0 !block"
                    wire:click="markAsRead({{ $notification->id }})"
                    href="{{ $targetUrl }}"
                >
                    <div class="flex gap-3">
                        <img src="{{ $notification->fromUser?->profileImageUrl('thumb') ?? auth()->user()->profileImageUrl('thumb') }}" class="size-8 rounded-full flex-shrink-0" alt="">
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <span class="font-bold text-xs truncate">{{ $notification->title }}</span>
                                @if(!$notification->read_at)
                                    <span class="size-2 bg-blue-500 rounded-full"></span>
                                @endif
                            </div>
                            <p class="text-xs text-zinc-500 line-clamp-2 mt-1">{{ $notification->message }}</p>
                            <span class="text-[10px] text-zinc-400 mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </flux:menu.item>
            @empty
                <div class="p-8 text-center text-zinc-500 italic">
                    <flux:icon icon="bell-off" class="size-8 mx-auto mb-2 opacity-20" />
                    <flux:text size="sm">{{ __('All caught up!') }}</flux:text>
                </div>
            @endforelse
        </div>

        @if($this->notifications->isNotEmpty())
            <div class="p-2 border-t border-zinc-100 dark:border-zinc-800 text-center">
                <flux:link href="#" class="text-xs">{{ __('View all activity') }}</flux:link>
            </div>
        @endif
    </flux:menu>
</flux:dropdown>
