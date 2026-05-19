<div class="space-y-4" x-data="{
    onDragStart(event, eventId) {
        event.dataTransfer.setData('eventId', eventId);
        event.dataTransfer.effectAllowed = 'move';
    },
    onDrop(event, date) {
        const eventId = event.dataTransfer.getData('eventId');
        if (eventId) {
            $wire.onEventDropped(eventId, date);
        }
    }
}" @dragover.prevent="">
    {{-- Header: mobile-first --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading level="1">Operational Calendar</flux:heading>
            <flux:subheading>Actions, events, and planned training in one scheduling view.</flux:subheading>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-2 rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">
                <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span> Events
            </span>
            <span class="inline-flex items-center gap-2 rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Actions
            </span>
            <span class="inline-flex items-center gap-2 rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Planned Training
            </span>
            <flux:button variant="primary" icon="plus" wire:click="$set('showCreateModal', true)" class="min-h-[44px] w-full sm:w-auto">
                New Event
            </flux:button>
        </div>
    </div>

    {{-- Calendar --}}
    <flux:card class="!p-0 overflow-hidden">
        {{-- Month Navigation --}}
        <div class="flex items-center justify-between px-4 py-3 sm:px-6 sm:py-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
            <div class="flex items-center gap-2">
                <flux:button wire:click="goToCurrentMonth" variant="ghost" size="sm" class="hidden sm:inline-flex">Today</flux:button>
                <div class="flex items-center border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                    <flux:button icon="chevron-left" wire:click="previousMonth" variant="ghost" size="sm" class="!rounded-none border-r border-zinc-200 dark:border-zinc-700 h-9 w-9" />
                    <flux:button icon="chevron-right" wire:click="nextMonth" variant="ghost" size="sm" class="!rounded-none h-9 w-9" />
                </div>
            </div>

            <flux:heading level="3" class="text-base sm:text-lg font-semibold">{{ $currentMonth->format('F Y') }}</flux:heading>

            <div class="flex items-center gap-2 sm:hidden">
                <flux:button wire:click="goToCurrentMonth" variant="ghost" size="sm">Today</flux:button>
            </div>
        </div>

        {{-- Mobile Agenda List --}}
        <div class="sm:hidden divide-y divide-zinc-200 dark:divide-zinc-700">
            @php
                $mobileDays = collect($this->calendarDays)
                    ->filter(function ($day) use ($currentMonth) {
                        return $day->month === $currentMonth->month;
                    })
                    ->filter(function ($day) {
                        $dateKey = $day->format('Y-m-d');
                        $dayItems = $this->calendarItems[$dateKey] ?? collect();

                        return $day->isToday() || $dayItems->isNotEmpty();
                    });
            @endphp

            @if($mobileDays->isEmpty())
                <div class="p-6 text-center">
                    <flux:text variant="subtle">No scheduled items this month.</flux:text>
                </div>
            @else
                @foreach($mobileDays as $day)
                    @php
                        $dateKey = $day->format('Y-m-d');
                        $dayItems = $this->calendarItems[$dateKey] ?? collect();
                    @endphp
                    <button type="button"
                            wire:click="selectDate('{{ $dateKey }}')"
                            class="w-full text-left p-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <flux:heading level="4" class="text-sm">
                                    {{ $day->format('D, M j') }}
                                </flux:heading>
                                <flux:text size="xs" variant="subtle">
                                    {{ $day->isToday() ? 'Today' : $day->format('Y') }}
                                </flux:text>
                            </div>
                            <flux:badge size="sm" color="{{ $day->isToday() ? 'blue' : 'zinc' }}">
                                {{ $dayItems->count() }} item{{ $dayItems->count() === 1 ? '' : 's' }}
                            </flux:badge>
                        </div>
                        @if($dayItems->isNotEmpty())
                            <div class="mt-2 space-y-1">
                                @foreach($dayItems->take(5) as $item)
                                    @if($item['type'] === 'event')
                                        <div class="flex items-center gap-2 rounded px-2 py-1 text-xs transition hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer" wire:click.stop="editEvent({{ $item['id'] }})">
                                            <span class="h-3 w-3 flex-shrink-0 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $item['time_label'] }}</span>
                                            <span class="truncate text-zinc-600 dark:text-zinc-400">{{ $item['title'] }}</span>
                                        </div>
                                    @elseif($item['url'])
                                        <div class="flex items-center gap-2 rounded px-2 py-1 text-xs transition hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                            <span class="h-3 w-3 flex-shrink-0 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $item['time_label'] }}</span>
                                            <span class="truncate text-zinc-600 dark:text-zinc-400">{{ $item['title'] }}</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 rounded px-2 py-1 text-xs">
                                            <span class="h-3 w-3 flex-shrink-0 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $item['time_label'] }}</span>
                                            <span class="truncate text-zinc-600 dark:text-zinc-400">{{ $item['title'] }}</span>
                                        </div>
                                    @endif
                                @endforeach
                                @if($dayItems->count() > 5)
                                    <flux:text size="xs" variant="subtle" class="pl-7">+{{ $dayItems->count() - 5 }} more</flux:text>
                                @endif
                            </div>
                        @endif
                    </button>
                @endforeach
            @endif
        </div>

        {{-- Day Headers --}}
        <div class="hidden sm:grid grid-cols-7 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                <div class="py-2 text-center text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        {{-- Calendar Grid --}}
        <div class="hidden sm:grid grid-cols-7">
            @foreach($this->calendarDays as $day)
                @php
                    $isCurrentMonth = $day->month === $currentMonth->month;
                    $isToday = $day->isToday();
                    $dateKey = $day->format('Y-m-d');
                    $dayItems = $this->calendarItems[$dateKey] ?? collect();
                @endphp
                <div wire:click="selectDate('{{ $dateKey }}')"
                     @drop="onDrop($event, '{{ $dateKey }}')"
                     class="min-h-24 p-2 border-b border-r border-zinc-100 dark:border-zinc-700/50 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition relative
                            {{ !$isCurrentMonth ? 'bg-zinc-50/50 dark:bg-zinc-900/50' : '' }}
                            {{ $isToday ? 'ring-1 ring-inset ring-blue-500 bg-blue-50/30 dark:bg-blue-900/10' : '' }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium w-6 h-6 flex items-center justify-center rounded-full
                            {{ $isToday ? 'bg-blue-600 text-white' : ($isCurrentMonth ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400 dark:text-zinc-600') }}">
                            {{ $day->format('j') }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        @foreach($dayItems->take(4) as $item)
                            @if($item['type'] === 'event')
                                <div class="cursor-pointer truncate rounded px-2 py-1 text-[10px] text-white shadow-sm transition-all duration-150 hover:brightness-110 active:scale-95 sm:text-xs"
                                     draggable="true"
                                     @dragstart="onDragStart($event, {{ $item['id'] }})"
                                     wire:click.stop="editEvent({{ $item['id'] }})"
                                     style="background-color: {{ $item['color'] }};">
                                    <span class="mr-1 font-bold opacity-90">{{ $item['time_label'] }}</span>
                                    {{ $item['title'] }}
                                </div>
                            @elseif($item['url'])
                                <a href="{{ $item['url'] }}" wire:navigate wire:click.stop class="block truncate rounded px-2 py-1 text-[10px] text-white shadow-sm transition-all duration-150 hover:brightness-110 sm:text-xs"
                                   style="background-color: {{ $item['color'] }};">
                                    <span class="mr-1 font-bold opacity-90">{{ $item['time_label'] }}</span>
                                    {{ $item['title'] }}
                                </a>
                            @else
                                <div class="truncate rounded px-2 py-1 text-[10px] text-white shadow-sm sm:text-xs"
                                     style="background-color: {{ $item['color'] }};">
                                    <span class="mr-1 font-bold opacity-90">{{ $item['time_label'] }}</span>
                                    {{ $item['title'] }}
                                </div>
                            @endif
                        @endforeach

                        @if($dayItems->count() > 4)
                            <div class="text-[10px] text-zinc-500 dark:text-zinc-400 pl-1 font-medium italic">
                                +{{ $dayItems->count() - 4 }} more scheduled
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </flux:card>

    {{-- Create/Edit Event Modal --}}
    <flux:modal name="create-event-modal" class="w-[calc(100vw-2rem)] sm:max-w-xl md:max-w-2xl mx-4 sm:mx-auto" wire:model="showCreateModal">
        <livewire:calendar.create-event-modal
            :selected-date="$selectedDate"
            :event-id="$editingEventId"
            wire:key="event-modal-{{ $selectedDate }}-{{ $editingEventId }}"
            @close="$set('showCreateModal', false)"
            @saved="$set('showCreateModal', false)" />
    </flux:modal>
</div>
