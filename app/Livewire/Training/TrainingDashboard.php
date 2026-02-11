<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Models\TrainingGoal;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TrainingDashboard extends Component
{
    #[Computed]
    public function activeGoals()
    {
        return Auth::user()->trainingGoals()
            ->with(['partner', 'milestones'])
            ->where('status', \App\Enums\TrainingGoalStatus::Active)
            ->get();
    }

    #[Computed]
    public function partnerGoals()
    {
        return Auth::user()->partnerGoals()
            ->with(['user', 'milestones'])
            ->where('partner_status', \App\Enums\PartnerStatus::Accepted)
            ->whereIn('status', [\App\Enums\TrainingGoalStatus::Active, \App\Enums\TrainingGoalStatus::Completed])
            ->get();
    }

    #[Computed]
    public function pendingRequests()
    {
        return Auth::user()->partnerGoals()
            ->with('user')
            ->where('partner_status', \App\Enums\PartnerStatus::Pending)
            ->get();
    }

    #[Computed]
    public function stats()
    {
        $user = Auth::user();
        return [
            'completed' => $user->trainingGoals()->whereIn('status', [\App\Enums\TrainingGoalStatus::Completed, \App\Enums\TrainingGoalStatus::Verified])->count(),
            'hours' => round($user->trainingGoals()->sum('logged_minutes') / 60, 1),
            'partner_count' => $this->partnerGoals()->count(),
        ];
    }

    public function render()
    {
        return view('livewire.training.training-dashboard')
            ->layout('layouts.app');
    }
}
