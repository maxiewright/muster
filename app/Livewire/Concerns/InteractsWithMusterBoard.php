<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Enums\MusterTaskStatus;
use App\Models\Muster;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

trait InteractsWithMusterBoard
{
    public CarbonImmutable $selectedDate;

    public ?int $expandedMusterId = null;

    public function mount(): void
    {
        $this->selectedDate = today();
    }

    public function previousDay(): void
    {
        $this->selectedDate = $this->selectedDate->copy()->subDay();
        $this->expandedMusterId = null;
    }

    public function nextDay(): void
    {
        if ($this->selectedDate->lt(today())) {
            $this->selectedDate = $this->selectedDate->copy()->addDay();
            $this->expandedMusterId = null;
        }
    }

    public function goToToday(): void
    {
        $this->selectedDate = today();
        $this->expandedMusterId = null;
    }

    public function toggleExpand(int $musterId): void
    {
        $this->expandedMusterId = $this->expandedMusterId === $musterId ? null : $musterId;
    }

    #[Computed]
    public function musters()
    {
        $activeUnitId = Auth::user()?->activeUnitId();

        return Muster::with(['user', 'tasks', 'focusAreas'])
            ->inUnit($activeUnitId)
            ->where('date', $this->selectedDate)
            ->latest()
            ->get();
    }

    #[Computed]
    public function teamMembers()
    {
        $activeUnit = Auth::user()?->activeUnit();

        if ($activeUnit === null) {
            return User::query()->get();
        }

        return $activeUnit->users()
            ->orderBy('users.name')
            ->get();
    }

    #[Computed]
    public function checkedInUsers(): array
    {
        return $this->musters->pluck('user_id')->toArray();
    }

    #[Computed]
    public function myMuster(): ?Muster
    {
        return $this->musters->where('user_id', auth()->id())->first();
    }

    #[Computed]
    public function stats(): array
    {
        $musters = $this->musters;

        return [
            'total_checkins' => $musters->count(),
            'total_team' => $this->teamMembers->count(),
            'tasks_planned' => $musters->sum(fn ($muster) => $muster->tasks->where('pivot.status', MusterTaskStatus::Planned->value)->count()),
            'tasks_completed' => $musters->sum(fn ($muster) => $muster->tasks->where('pivot.status', MusterTaskStatus::Completed->value)->count()),
            'blockers' => $musters->filter(fn ($muster): bool => ! empty($muster->blockers))->count(),
            'moods' => $musters->groupBy(fn ($muster) => $muster->mood?->value)->map->count(),
        ];
    }

    #[On('echo:muster,MusterCreated')]
    public function handleNewMuster(): void
    {
        unset($this->musters);
        unset($this->stats);
    }

    public function render(): Factory|View
    {
        return view('components.muster.⚡muster-board.muster-board');
    }
}
