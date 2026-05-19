<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Enums\PartnerStatus;
use App\Enums\TrainingGoalStatus;
use App\Models\TrainingGoal;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TrainingDashboard extends Component
{
    #[Computed]
    public function canManageAssignments(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->canAssignUnitTraining($user->activeUnit());
    }

    #[Computed]
    public function activeGoals()
    {
        $user = Auth::user();

        return TrainingGoal::query()
            ->where('user_id', $user->id)
            ->inUnit($user->activeUnitId())
            ->with(['partner', 'focusArea', 'milestones', 'checkins'])
            ->where('status', TrainingGoalStatus::Active)
            ->get();
    }

    #[Computed]
    public function partnerGoals()
    {
        $user = Auth::user();

        return TrainingGoal::query()
            ->where('accountability_partner_id', $user->id)
            ->inUnit($user->activeUnitId())
            ->with(['user', 'focusArea', 'milestones', 'checkins'])
            ->where('partner_status', PartnerStatus::Accepted)
            ->whereIn('status', [TrainingGoalStatus::Active, TrainingGoalStatus::Completed])
            ->get();
    }

    #[Computed]
    public function pendingRequests()
    {
        $user = Auth::user();

        return TrainingGoal::query()
            ->where('accountability_partner_id', $user->id)
            ->inUnit($user->activeUnitId())
            ->with('user')
            ->where('partner_status', PartnerStatus::Pending)
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $user = Auth::user();
        $activeUnitId = $user->activeUnitId();

        return [
            'completed' => TrainingGoal::query()
                ->where('user_id', $user->id)
                ->inUnit($activeUnitId)
                ->whereIn('status', [TrainingGoalStatus::Completed, TrainingGoalStatus::Verified])
                ->count(),
            'hours' => round(TrainingGoal::query()
                ->where('user_id', $user->id)
                ->inUnit($activeUnitId)
                ->sum('logged_minutes') / 60, 1),
            'partner_count' => $this->partnerGoals()->count(),
        ];
    }

    public function render()
    {
        return view('livewire.training.training-dashboard')
            ->layout('layouts.app');
    }
}
