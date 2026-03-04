<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Models\TrainingMilestone;
use App\Services\TrainingGamificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MilestoneCompleteModal extends Component
{
    public TrainingMilestone $milestone;

    public string $notes = '';

    public string $evidence_url = '';

    public function mount(TrainingMilestone $milestone): void
    {
        $this->milestone = $milestone;
    }

    public function submit(TrainingGamificationService $gamification): void
    {
        abort_unless($this->milestone->goal->user_id === Auth::id(), 403);

        $this->milestone->markAsCompleted($this->notes, $this->evidence_url);
        $gamification->onMilestoneCompleted($this->milestone);

        $this->dispatch('closeModal');
        $this->dispatch('milestone-completed');
        session()->flash('status', 'Milestone marked as complete!');
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.training.milestone-complete-modal');
    }
}
