<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public CarbonImmutable $currentMonth;

    public bool $showCreateModal = false;

    public ?string $selectedDate = null;

    public ?int $editingEventId = null;

    public function mount(): void
    {
        $this->currentMonth = CarbonImmutable::now()->startOfMonth();
    }

    public function previousMonth(): void
    {
        $this->currentMonth = $this->currentMonth->copy()->subMonth();
    }

    public function nextMonth(): void
    {
        $this->currentMonth = $this->currentMonth->copy()->addMonth();
    }

    public function goToCurrentMonth(): void
    {
        $this->currentMonth = CarbonImmutable::now()->startOfMonth();
    }

    public function selectDate(string $date): void
    {
        $this->editingEventId = null;
        $this->selectedDate = $date;
        $this->showCreateModal = true;
    }

    public function editEvent(int $eventId): void
    {
        $this->selectedDate = null;
        $this->editingEventId = $eventId;
        $this->showCreateModal = true;
    }

    public function onEventDropped(int $eventId, string $date): void
    {
        $event = \App\Models\Event::findOrFail($eventId);
        $newDate = CarbonImmutable::parse($date);

        // Preserve original time
        $originalStartsAt = $event->starts_at;
        $event->starts_at = $newDate->setTime($originalStartsAt->hour, $originalStartsAt->minute);

        if ($event->ends_at) {
            $duration = $originalStartsAt->diffInSeconds($event->ends_at);
            $event->ends_at = $event->starts_at->addSeconds($duration);
        }

        $event->save();
    }

    #[Computed]
    public function events(): Collection
    {
        return \App\Models\Event::with(['user', 'type'])
            ->whereMonth('starts_at', $this->currentMonth->month)
            ->whereYear('starts_at', $this->currentMonth->year)
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn ($event) => $event->starts_at->format('Y-m-d'));
    }

    #[Computed]
    public function calendarDays(): array
    {
        $start = $this->currentMonth->copy()->startOfWeek(CarbonImmutable::MONDAY);
        $end = $this->currentMonth->copy()->endOfMonth()->endOfWeek(CarbonImmutable::SUNDAY);

        $days = [];
        $current = $start->copy();

        while ($current <= $end) {
            $days[] = $current->copy();
            $current = $current->addDay();
        }

        return $days;
    }

    public function render()
    {
        return view('components.calendar.⚡calendar-view.calendar-view');
    }
};
