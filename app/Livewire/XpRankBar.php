<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\ProgressTier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class XpRankBar extends Component
{
    public int $points = 0;

    public string $rankValue = '';

    public string $rankLabel = '';

    public string $rankIcon = '';

    public ?string $nextRankLabel = null;

    public int $nextRankPoints = 0;

    public int $progressPercent = 0;

    public function mount(): void
    {
        $this->refreshXp();
    }

    #[On('points-awarded')]
    #[On('task-completed')]
    public function refreshXp(): void
    {
        $user = auth()->user();
        $this->points = $user->fresh()->points ?? 0;

        $tier = ProgressTier::fromPoints($this->points);
        $this->rankValue = $tier->value;
        $this->rankLabel = $tier->label();
        $this->rankIcon = $tier->icon();
        $this->progressPercent = $tier->progressToNext($this->points);

        $nextTier = $tier->nextTier();
        $this->nextRankLabel = $nextTier?->label();
        $this->nextRankPoints = $nextTier?->minPoints() ?? $this->points;
    }

    public function render(): View
    {
        return view('livewire.xp-rank-bar');
    }
}
