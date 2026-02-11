<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Models\TrainingGoal;
use App\Models\PartnerNotification;
use App\Models\TrainingMilestone;
use App\Models\TrainingCheckin;
use App\Enums\PartnerStatus;
use App\Enums\MilestoneStatus;
use App\Enums\TrainingGoalStatus;
use App\Services\TrainingGamificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TrainingGoalShow extends Component
{
    public TrainingGoal $goal;

    public function mount(TrainingGoal $goal): void
    {
        $this->goal = $goal->load(['user', 'partner', 'milestones', 'checkins.user', 'checkins.feedbackProvider']);
        
        // Mark notifications as read if this is the partner/owner
        if (Auth::id() === $this->goal->user_id || Auth::id() === $this->goal->accountability_partner_id) {
            PartnerNotification::where('training_goal_id', $this->goal->id)
                ->where('user_id', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    #[Computed]
    public function canEdit(): bool
    {
        return $this->goal->canBeEditedBy(Auth::user());
    }

    #[Computed]
    public function canVerify(): bool
    {
        return $this->goal->canBeVerifiedBy(Auth::user());
    }

    #[Computed]
    public function isPartner(): bool
    {
        return $this->goal->accountability_partner_id === Auth::id();
    }

    #[Computed]
    public function isOwner(): bool
    {
        return $this->goal->user_id === Auth::id();
    }

    public function verifyMilestone(int $milestoneId, TrainingGamificationService $gamification): void
    {
        if (!$this->canVerify) {
            return;
        }

        $milestone = TrainingMilestone::findOrFail($milestoneId);
        $milestone->verify(Auth::user());
        
        $gamification->onMilestoneVerified($milestone, Auth::user());
        
        $this->goal->refresh();
        $this->dispatch('milestone-verified');
        session()->flash('status', 'Milestone verified!');
    }

    public function verifyGoal(TrainingGamificationService $gamification): void
    {
        if (!$this->canVerify) {
            return;
        }

        $this->goal->verify(Auth::user());
        $gamification->onGoalVerified($this->goal, Auth::user());
        
        session()->flash('status', 'Goal verified and completed!');
    }

    public function acceptPartnerRequest(TrainingGamificationService $gamification): void
    {
        if (!$this->isPartner) {
            return;
        }

        $this->goal->update(['partner_status' => PartnerStatus::Accepted]);
        $gamification->onGoalActivated($this->goal);
        
        session()->flash('status', 'Partner request accepted!');
        $this->goal->refresh();
    }

    public function declinePartnerRequest(?string $reason = null): void
    {
        if (!$this->isPartner) {
            return;
        }

        $this->goal->update([
            'partner_status' => PartnerStatus::Declined,
            'partner_decline_reason' => $reason
        ]);
        
        session()->flash('status', 'Partner request declined.');
        $this->redirect(route('training.dashboard'));
    }

    public function render()
    {
        return view('livewire.training.training-goal-show')
            ->layout('layouts.app');
    }
}
