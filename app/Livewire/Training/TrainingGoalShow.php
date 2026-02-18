<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Enums\PartnerStatus;
use App\Models\PartnerNotification;
use App\Models\TrainingGoal;
use App\Services\TrainingGamificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TrainingGoalShow extends Component
{
    public TrainingGoal $goal;

    public function mount(TrainingGoal $goal): void
    {
        $this->goal = $goal->load(['user', 'partner', 'milestones', 'checkins.user', 'checkins.feedbackProvider']);

        // Enforce visibility: private goals are only accessible by the owner or accepted partner.
        if (! $this->goal->is_public) {
            abort_unless(
                Auth::id() === $this->goal->user_id || Auth::id() === $this->goal->accountability_partner_id,
                403
            );
        }

        // Mark notifications as read for the owner or partner.
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
        // Re-check against live DB state to prevent stale cached computed values from being exploited.
        abort_unless($this->goal->fresh()->canBeVerifiedBy(Auth::user()), 403);

        // Scope to this goal's milestones to prevent cross-goal manipulation.
        $milestone = $this->goal->milestones()->findOrFail($milestoneId);
        $milestone->verify(Auth::user());

        $gamification->onMilestoneVerified($milestone, Auth::user());

        $this->goal->refresh();
        $this->dispatch('milestone-verified');
        session()->flash('status', 'Milestone verified!');
    }

    public function verifyGoal(TrainingGamificationService $gamification): void
    {
        // Re-check against live DB state to prevent stale cached computed values from being exploited.
        abort_unless($this->goal->fresh()->canBeVerifiedBy(Auth::user()), 403);

        $this->goal->verify(Auth::user());
        $gamification->onGoalVerified($this->goal, Auth::user());

        session()->flash('status', 'Goal verified and completed!');
    }

    public function acceptPartnerRequest(TrainingGamificationService $gamification): void
    {
        // Re-check that the current user is still the partner on this goal in the DB.
        abort_unless($this->goal->fresh()->accountability_partner_id === Auth::id(), 403);

        $this->goal->update(['partner_status' => PartnerStatus::Accepted]);
        $gamification->onGoalActivated($this->goal);

        session()->flash('status', 'Partner request accepted!');
        $this->goal->refresh();
    }

    public function declinePartnerRequest(?string $reason = null): void
    {
        // Re-check that the current user is still the partner on this goal in the DB.
        abort_unless($this->goal->fresh()->accountability_partner_id === Auth::id(), 403);

        $this->goal->update([
            'partner_status' => PartnerStatus::Declined,
            'partner_decline_reason' => $reason,
        ]);

        session()->flash('status', 'Partner request declined.');
        $this->redirect(route('training.dashboard'));
    }

    public function render(): View
    {
        return view('livewire.training.training-goal-show')
            ->layout('layouts.app');
    }
}
