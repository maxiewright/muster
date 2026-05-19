<?php

use App\Enums\TrainingGoalStatus;
use App\Models\Event;
use App\Models\Task;
use App\Models\TrainingGoal;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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
        $event = Event::query()
            ->inUnit(auth()->user()?->activeUnitId())
            ->findOrFail($eventId);
        $this->authorize('update', $event);
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
        $activeUnitId = auth()->user()?->activeUnitId();

        return Event::query()
            ->inUnit($activeUnitId)
            ->whereMonth('starts_at', $this->currentMonth->month)
            ->whereYear('starts_at', $this->currentMonth->year)
            ->with(['user', 'type'])
            ->oldest('starts_at')
            ->get();
    }

    #[Computed]
    public function calendarItems(): Collection
    {
        $user = auth()->user();
        $activeUnitId = $user?->activeUnitId();

        $eventItems = $this->events->map(fn (Event $event): array => [
            'type' => 'event',
            'id' => $event->id,
            'date' => $event->starts_at->format('Y-m-d'),
            'title' => $event->title,
            'subtitle' => 'Event',
            'time_label' => $event->starts_at->format('H:i'),
            'sort_at' => $event->starts_at->timestamp,
            'color' => $event->typeColor ?? '#2563eb',
            'url' => null,
        ]);

        $actionItems = Task::query()
            ->inUnit($activeUnitId)
            ->whereNotNull('due_date')
            ->whereMonth('due_date', $this->currentMonth->month)
            ->whereYear('due_date', $this->currentMonth->year)
            ->with('mission')
            ->oldest('due_date')
            ->get()
            ->map(fn (Task $task): array => [
                'type' => 'action',
                'id' => $task->id,
                'date' => $task->due_date->format('Y-m-d'),
                'title' => $task->title,
                'subtitle' => $task->mission?->name ? 'Action • '.$task->mission->name : 'Action',
                'time_label' => 'Due',
                'sort_at' => $task->due_date->startOfDay()->timestamp,
                'color' => '#f59e0b',
                'url' => route('tasks'),
            ]);

        $trainingItems = TrainingGoal::query()
            ->inUnit($activeUnitId)
            ->whereNotNull('target_date')
            ->where('status', TrainingGoalStatus::Active)
            ->whereMonth('target_date', $this->currentMonth->month)
            ->whereYear('target_date', $this->currentMonth->year)
            ->with('user')
            ->when($user instanceof User, function ($query) use ($user): void {
                $query->where(function ($trainingQuery) use ($user): void {
                    $trainingQuery->where('is_public', true)
                        ->orWhere('user_id', $user->id)
                        ->orWhere('accountability_partner_id', $user->id);

                    if ($user->isLead()) {
                        $trainingQuery->orWhere('is_unit_directed', true);
                    }
                });
            })
            ->oldest('target_date')
            ->get()
            ->map(function (TrainingGoal $goal) use ($user): array {
                $canOpen = $goal->is_public
                    || $goal->user_id === $user?->id
                    || $goal->accountability_partner_id === $user?->id;

                $title = $goal->title;

                if ($user?->isLead() && $goal->user && $goal->user_id !== $user->id) {
                    $title .= ' • '.$goal->user->name;
                }

                return [
                    'type' => 'training',
                    'id' => $goal->id,
                    'date' => $goal->target_date->format('Y-m-d'),
                    'title' => $title,
                    'subtitle' => $goal->is_unit_directed ? 'Planned Training • Unit Directed' : 'Planned Training',
                    'time_label' => 'Target',
                    'sort_at' => $goal->target_date->startOfDay()->timestamp,
                    'color' => '#10b981',
                    'url' => $canOpen ? route('training.goals.show', $goal->slug) : null,
                ];
            });

        return $eventItems
            ->concat($actionItems)
            ->concat($trainingItems)
            ->sortBy([
                ['sort_at', 'asc'],
                ['title', 'asc'],
            ])
            ->groupBy('date');
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

    public function render(): Factory|View
    {
        return view('components.calendar.⚡calendar-view.calendar-view');
    }
};
