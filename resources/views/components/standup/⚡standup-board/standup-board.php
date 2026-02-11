<?php

use App\Enums\StandupTaskStatus;
use App\Models\Standup;
use App\Models\User;
use Carbon\CarbonImmutable;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public CarbonImmutable $selectedDate;

    public ?int $expandedStandupId = null;

    public function mount(): void
    {
        $this->selectedDate = today();
    }

    public function previousDay(): void
    {
        $this->selectedDate = $this->selectedDate->copy()->subDay();
        $this->expandedStandupId = null;
    }

    public function nextDay(): void
    {
        if ($this->selectedDate->lt(today())) {
            $this->selectedDate = $this->selectedDate->copy()->addDay();
            $this->expandedStandupId = null;
        }
    }

    public function goToToday(): void
    {
        $this->selectedDate = today();
        $this->expandedStandupId = null;
    }

    public function toggleExpand(int $standupId): void
    {
        $this->expandedStandupId = $this->expandedStandupId === $standupId ? null : $standupId;
    }

    #[Computed]
    public function standups()
    {
        return Standup::with(['user', 'tasks', 'focusAreas'])
            ->where('date', $this->selectedDate)
            ->latest()
            ->get();
    }

    #[Computed]
    public function teamMembers()
    {
        return User::all();
    }

    #[Computed]
    public function checkedInUsers(): array
    {
        return $this->standups->pluck('user_id')->toArray();
    }

    #[Computed]
    public function myStandup(): ?Standup
    {
        return $this->standups->where('user_id', auth()->id())->first();
    }

    #[Computed]
    public function stats(): array
    {
        $standups = $this->standups;

        return [
            'total_checkins' => $standups->count(),
            'total_team' => $this->teamMembers->count(),
            'tasks_planned' => $standups->sum(fn ($s) => $s->tasks->where('pivot.status', StandupTaskStatus::Planned->value)->count()),
            'tasks_completed' => $standups->sum(fn ($s) => $s->tasks->where('pivot.status', StandupTaskStatus::Completed->value)->count()),
            'blockers' => $standups->filter(fn ($s) => ! empty($s->blockers))->count(),
            'moods' => $standups->groupBy(fn ($s) => $s->mood?->value)->map->count(),
        ];
    }

    #[On('echo:muster,StandupCreated')]
    public function handleNewStandup(): void
    {
        unset($this->standups);
        unset($this->stats);
    }

    public function render()
    {
        return view('components.standup.⚡standup-board.standup-board');
    }
};
