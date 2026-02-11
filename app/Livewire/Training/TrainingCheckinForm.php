<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Models\TrainingGoal;
use App\Models\TrainingCheckin;
use App\Models\TrainingMilestone;
use App\Enums\ConfidenceLevel;
use App\Services\TrainingGamificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TrainingCheckinForm extends Component
{
    public TrainingGoal $goal;
    
    public string $progress_update = '';
    public string $learnings = '';
    public string $blockers = '';
    public string $next_steps = '';
    public int $minutes_logged = 0;
    public string $confidence_level = '';
    public ?int $milestone_id = null;

    public function mount(TrainingGoal $goal): void
    {
        $this->goal = $goal;
        $this->confidence_level = ConfidenceLevel::OnTrack->value;
    }

    protected function rules(): array
    {
        return [
            'progress_update' => 'required|string',
            'learnings' => 'nullable|string',
            'blockers' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'minutes_logged' => 'required|integer|min:0',
            'confidence_level' => 'required|string',
            'milestone_id' => 'nullable|exists:training_milestones,id',
        ];
    }

    #[Computed]
    public function milestones()
    {
        return $this->goal->milestones()
            ->whereIn('status', [\App\Enums\MilestoneStatus::Pending, \App\Enums\MilestoneStatus::Completed])
            ->get();
    }

    #[Computed]
    public function confidenceLevels()
    {
        return ConfidenceLevel::cases();
    }

    public function save(TrainingGamificationService $gamification): void
    {
        $this->validate();

        $checkin = $this->goal->checkins()->create([
            'user_id' => Auth::id(),
            'milestone_id' => $this->milestone_id,
            'progress_update' => $this->progress_update,
            'learnings' => $this->learnings,
            'blockers' => $this->blockers,
            'next_steps' => $this->next_steps,
            'minutes_logged' => $this->minutes_logged,
            'confidence_level' => $this->confidence_level,
        ]);

        // If milestone was selected, mark it as completed (but not verified yet)
        if ($this->milestone_id) {
            $milestone = TrainingMilestone::find($this->milestone_id);
            if ($milestone instanceof TrainingMilestone && $milestone->status === \App\Enums\MilestoneStatus::Pending) {
                $milestone->markAsCompleted($this->progress_update);
                $gamification->onMilestoneCompleted($milestone);
            }
        }

        $gamification->onCheckinLogged($checkin);

        session()->flash('status', 'Progress logged successfully!');
        $this->redirect(route('training.goals.show', $this->goal->slug));
    }

    public function render()
    {
        return view('livewire.training.training-checkin-form')
            ->layout('layouts.app');
    }
}
