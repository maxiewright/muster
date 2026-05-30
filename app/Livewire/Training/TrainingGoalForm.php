<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Enums\MilestoneStatus;
use App\Enums\PartnerStatus;
use App\Enums\TrainingCategory;
use App\Enums\TrainingGoalStatus;
use App\Livewire\Forms\GoalForm;
use App\Models\FocusArea;
use App\Models\PartnerNotification;
use App\Models\TrainingGoal;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TrainingGoalForm extends Component
{
    public ?TrainingGoal $goal = null;

    public GoalForm $form;

    public bool $isEditing = false;

    public int $step = 1;

    public function mount(?TrainingGoal $goal = null): void
    {
        $this->form->start_date = now()->toDateString();
        $this->form->target_date = now()->addMonth()->toDateString();

        if ($goal instanceof TrainingGoal) {
            abort_unless($goal->canBeEditedBy(Auth::user()), 403);

            $this->goal = $goal->load('milestones');
            $this->isEditing = true;

            $this->form->setGoal($goal);
        }
    }

    #[Computed]
    public function categories(): array
    {
        return TrainingCategory::cases();
    }

    #[Computed]
    public function focusAreas()
    {
        return FocusArea::query()->orderBy('name')->get();
    }

    #[Computed]
    public function users()
    {
        $user = Auth::user();
        $activeUnit = $user->activeUnit();

        if (! $activeUnit instanceof Unit) {
            return User::query()
                ->where('organization_id', $user->organization_id)
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->get();
        }

        return $activeUnit->users()
            ->where('users.id', '!=', $user->id)
            ->orderBy('users.name')
            ->get();
    }

    public function addMilestone(): void
    {
        $this->form->milestones[] = ['title' => '', 'target_date' => null];
    }

    public function removeMilestone(int $index): void
    {
        unset($this->form->milestones[$index]);

        if ($this->form->milestones === []) {
            $this->form->milestones[] = ['title' => '', 'target_date' => null];
        }

        $this->form->milestones = array_values($this->form->milestones);
    }

    public function nextStep(): void
    {
        $this->validateStep($this->step);

        if ($this->step < 4) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function save(): void
    {
        $user = Auth::user();
        $activeUnit = $user->activeUnit();

        abort_unless($activeUnit instanceof Unit, 403);

        if ($this->isEditing && $this->goal) {
            abort_unless($this->goal->canBeEditedBy($user), 403);
        }

        $this->form->validate();

        $payload = [
            'organization_id' => $user->organization_id,
            'unit_id' => $activeUnit->id,
            'title' => $this->form->title,
            'description' => $this->form->description,
            'success_criteria' => $this->form->success_criteria,
            'category' => $this->form->category,
            'focus_area_id' => $this->form->focus_area_id,
            'start_date' => $this->form->start_date,
            'target_date' => $this->form->target_date,
            'accountability_partner_id' => $this->form->accountability_partner_id,
            'is_public' => $this->form->is_public,
            'status' => TrainingGoalStatus::Active,
            'partner_status' => $this->form->accountability_partner_id ? PartnerStatus::Pending : PartnerStatus::None,
        ];

        if ($this->isEditing && $this->goal) {
            $this->goal->update($payload);
            $goal = $this->goal;

            $goal->milestones()->delete();
        } else {
            $goal = $user->trainingGoals()->create($payload);
        }

        $rows = collect($this->form->milestones)
            ->filter(fn (array $milestone): bool => trim($milestone['title'] ?? '') !== '')
            ->values();

        foreach ($rows as $index => $milestone) {
            $goal->milestones()->create([
                'title' => trim((string) $milestone['title']),
                'target_date' => $milestone['target_date'] ?: null,
                'order' => $index + 1,
                'status' => MilestoneStatus::Pending,
            ]);
        }

        if (! $this->isEditing && $goal->accountability_partner_id) {
            $existingRequest = PartnerNotification::query()
                ->where('user_id', $goal->accountability_partner_id)
                ->where('from_user_id', Auth::id())
                ->where('training_goal_id', $goal->id)
                ->where('type', 'partner_request')
                ->whereNull('actioned_at')
                ->first();

            if (! $existingRequest) {
                PartnerNotification::query()->create([
                    'organization_id' => $goal->organization_id,
                    'unit_id' => $goal->unit_id,
                    'user_id' => $goal->accountability_partner_id,
                    'from_user_id' => $user->id,
                    'training_goal_id' => $goal->id,
                    'type' => 'partner_request',
                    'title' => 'New partner request',
                    'message' => $user->name.' invited you to support: '.$goal->title,
                ]);
            }
        }

        session()->flash('status', $this->isEditing ? 'Training goal updated.' : 'Training goal created.');

        $this->redirectRoute('training.goals.show', ['goal' => $goal->slug], navigate: true);
    }

    protected function validateStep(int $step): void
    {
        if ($step === 1) {
            $this->validate([
                'form.title' => ['required', 'string', 'max:255'],
                'form.category' => ['required', 'string'],
                'form.focus_area_id' => ['required', 'integer', 'exists:focus_areas,id'],
                'form.start_date' => ['required', 'date'],
                'form.target_date' => ['required', 'date', 'after_or_equal:form.start_date'],
            ]);
        }

        if ($step === 2) {
            $this->validate([
                'form.description' => ['required', 'string'],
                'form.success_criteria' => ['required', 'string'],
            ]);
        }
    }

    public function render()
    {
        return view('livewire.training.training-goal-form')
            ->layout('layouts.app');
    }
}
