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
        <flux:heading level="1">Team Calendar</flux:heading>
        <flux:button variant="primary" icon="plus" wire:click="$set('showCreateModal', true)" class="min-h-[44px] w-full sm:w-auto">
            New Event
        </flux:button>
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
                        $dayEvents = $this->events[$dateKey] ?? collect();

                        return $day->isToday() || $dayEvents->isNotEmpty();
                    });
            @endphp

            @if($mobileDays->isEmpty())
                <div class="p-6 text-center">
                    <flux:text variant="subtle">No events this month.</flux:text>
                </div>
            @else
                @foreach($mobileDays as $day)
                    @php
                        $dateKey = $day->format('Y-m-d');
                        $dayEvents = $this->events[$dateKey] ?? collect();
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
                                {{ $dayEvents->count() }} event{{ $dayEvents->count() === 1 ? '' : 's' }}
                            </flux:badge>
                        </div>
                        @if($dayEvents->isNotEmpty())
                            <div class="mt-2 space-y-1">
                                @foreach($dayEvents->take(5) as $event)
                                    <div class="flex items-center gap-2 text-xs py-1 px-2 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer" wire:click.stop="editEvent({{ $event->id }})">
                                        <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $event->typeColor }}"></span>
                                        <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $event->starts_at->format('H:i') }}</span>
                                        <span class="truncate text-zinc-600 dark:text-zinc-400">{{ $event->title }}</span>
                                    </div>
                                @endforeach
                                @if($dayEvents->count() > 5)
                                    <flux:text size="xs" variant="subtle" class="pl-7">+{{ $dayEvents->count() - 5 }} more</flux:text>
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
                    $dayEvents = $this->events[$dateKey] ?? collect();
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
                        @foreach($dayEvents->take(4) as $event)
                            <div class="text-[10px] sm:text-xs px-2 py-1 rounded shadow-sm truncate text-white cursor-pointer hover:brightness-110 active:scale-95 transition-all duration-150"
                                 draggable="true"
                                 @dragstart="onDragStart($event, {{ $event->id }})"
                                 wire:click.stop="editEvent({{ $event->id }})"
                                 style="background-color: {{ $event->typeColor }};">
                                <span class="font-bold opacity-90 mr-1">{{ $event->starts_at->format('H:i') }}</span>
                                {{ $event->title }}
                            </div>
                        @endforeach

                        @if($dayEvents->count() > 4)
                            <div class="text-[10px] text-zinc-500 dark:text-zinc-400 pl-1 font-medium italic">
                                +{{ $dayEvents->count() - 4 }} more events
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
