<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Enums\PartnerStatus;
use App\Models\TrainingGoal;
use App\Services\TrainingGamificationService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PartnerRequestModal extends Component
{
    public TrainingGoal $goal;

    public string $decline_reason = '';

    public function mount(TrainingGoal $goal): void
    {
        $this->goal = $goal;
        abort_unless($this->goal->belongsToUserContext(Auth::user()), 403);
    }

    public function accept(TrainingGamificationService $gamification): void
    {
        abort_unless($this->goal->belongsToUserContext(Auth::user()), 403);
        abort_unless($this->goal->accountability_partner_id === Auth::id(), 403);

        $this->goal->update(['partner_status' => PartnerStatus::Accepted]);
        $gamification->onGoalActivated($this->goal);

        $this->dispatch('closeModal');
        $this->dispatch('partner-request-accepted');
        session()->flash('status', 'Partner request accepted!');
    }

    public function decline(): void
    {
        abort_unless($this->goal->belongsToUserContext(Auth::user()), 403);
        abort_unless($this->goal->accountability_partner_id === Auth::id(), 403);

        $this->goal->update([
            'partner_status' => PartnerStatus::Declined,
            'partner_decline_reason' => $this->decline_reason,
        ]);

        $this->dispatch('closeModal');
        $this->redirect(route('training.dashboard'));
    }

    public function render(): Factory|View
    {
        return view('livewire.training.partner-request-modal');
    }
}
