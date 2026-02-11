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
        if ($this->milestone->goal->user_id !== Auth::id()) {
            return;
        }

        $this->milestone->markAsCompleted($this->notes, $this->evidence_url);
        $gamification->onMilestoneCompleted($this->milestone);
        
        $this->dispatch('closeModal');
        $this->dispatch('milestone-completed');
        session()->flash('status', 'Milestone marked as complete!');
    }

    public function render()
    {
        return view('livewire.training.milestone-complete-modal');
    }
}
