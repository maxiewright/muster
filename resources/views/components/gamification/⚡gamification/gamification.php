<?php

declare(strict_types=1);

use App\Models\Badge;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function user(): User
    {
        return auth()->user()->load(['badges', 'pointLogs.user']);
    }

    #[Computed]
    public function earnedBadges()
    {
        return $this->user->badges()->orderByPivot('earned_at', 'desc')->get();
    }

    #[Computed]
    public function recentPointLogs()
    {
        return $this->user->pointLogs()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();
    }

    #[Computed]
    public function leaderboard()
    {
        return User::query()
            ->orderByDesc('points')
            ->limit(10)
            ->get(['id', 'name', 'points', 'current_streak', 'longest_streak']);
    }

    #[Computed]
    public function allBadges()
    {
        return Badge::orderBy('slug')->get();
    }

    public function render()
    {
        return view('components.gamification.⚡gamification.gamification');
    }
};
