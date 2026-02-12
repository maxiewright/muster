<?php

namespace App\Livewire\Training;

use App\Enums\MilestoneStatus;
use App\Enums\PartnerStatus;
use App\Enums\TrainingCategory;
use App\Enums\TrainingGoalStatus;
use App\Models\FocusArea;
use App\Models\PartnerNotification;
use App\Models\TrainingGoal;
use App\Models\TrainingMilestone;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TrainingGoalForm extends Component
{
    public ?TrainingGoal $goal = null;

    public bool $isEditing = false;

    public int $step = 1;

    public string $title = '';

    public string $description = '';

    public string $success_criteria = '';

    public ?string $category = null;

    public ?int $focus_area_id = null;

    public ?string $start_date = null;

    public ?string $target_date = null;

    public ?int $accountability_partner_id = null;

    public bool $is_public = true;

    /** @var array<int, array{title:string, target_date:string|null}> */
    public array $milestones = [
        ['title' => '', 'target_date' => null],
    ];

    public function mount(?TrainingGoal $goal = null): void
    {
        $this->start_date = now()->toDateString();
        $this->target_date = now()->addMonth()->toDateString();

        if ($goal && $goal->user_id === Auth::id()) {
            $this->goal = $goal->load('milestones');
            $this->isEditing = true;

            $this->title = $goal->title;
            $this->description = (string) ($goal->description ?? '');
            $this->success_criteria = (string) ($goal->success_criteria ?? '');
            $this->category = $goal->category?->value;
            $this->focus_area_id = $goal->focus_area_id;
            $this->start_date = $goal->start_date?->toDateString();
            $this->target_date = $goal->target_date?->toDateString();
            $this->accountability_partner_id = $goal->accountability_partner_id;
            $this->is_public = (bool) $goal->is_public;

            $milestones = $goal->milestones
                ->sortBy('order')
                ->map(fn (TrainingMilestone $milestone): array => [
                    'title' => $milestone->title,
                    'target_date' => $milestone->target_date?->toDateString(),
                ])
                ->values()
                ->all();

            $this->milestones = $milestones !== [] ? $milestones : [['title' => '', 'target_date' => null]];
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
        return User::query()
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();
    }

    public function addMilestone(): void
    {
        $this->milestones[] = ['title' => '', 'target_date' => null];
    }

    public function removeMilestone(int $index): void
    {
        unset($this->milestones[$index]);

        if ($this->milestones === []) {
            $this->milestones[] = ['title' => '', 'target_date' => null];
        }

        $this->milestones = array_values($this->milestones);
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
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'success_criteria' => ['required', 'string'],
            'category' => ['required', 'string'],
            'focus_area_id' => ['required', 'integer', 'exists:focus_areas,id'],
            'start_date' => ['required', 'date'],
            'target_date' => ['required', 'date', 'after_or_equal:start_date'],
            'accountability_partner_id' => ['nullable', 'integer', 'exists:users,id'],
            'milestones' => ['array'],
            'milestones.*.title' => ['nullable', 'string', 'max:255'],
            'milestones.*.target_date' => ['nullable', 'date'],
            'is_public' => ['boolean'],
        ]);

        $payload = [
            'title' => $this->title,
            'description' => $this->description,
            'success_criteria' => $this->success_criteria,
            'category' => $this->category,
            'focus_area_id' => $this->focus_area_id,
            'start_date' => $this->start_date,
            'target_date' => $this->target_date,
            'accountability_partner_id' => $this->accountability_partner_id,
            'is_public' => $this->is_public,
            'status' => TrainingGoalStatus::Active,
            'partner_status' => $this->accountability_partner_id ? PartnerStatus::Pending : PartnerStatus::None,
        ];

        if ($this->isEditing && $this->goal) {
            $this->goal->update($payload);
            $goal = $this->goal;

            $goal->milestones()->delete();
        } else {
            $goal = Auth::user()->trainingGoals()->create($payload);
        }

        $rows = collect($this->milestones)
            ->filter(fn (array $milestone): bool => trim((string) ($milestone['title'] ?? '')) !== '')
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
            PartnerNotification::query()->create([
                'user_id' => $goal->accountability_partner_id,
                'from_user_id' => Auth::id(),
                'training_goal_id' => $goal->id,
                'type' => 'partner_request',
                'title' => 'New partner request',
                'message' => Auth::user()->name.' invited you to support: '.$goal->title,
            ]);
        }

        session()->flash('status', $this->isEditing ? 'Training goal updated.' : 'Training goal created.');

        $this->redirectRoute('training.goals.show', ['goal' => $goal->slug], navigate: true);
    }

    protected function validateStep(int $step): void
    {
        if ($step === 1) {
            $this->validate([
                'title' => ['required', 'string', 'max:255'],
                'category' => ['required', 'string'],
                'focus_area_id' => ['required', 'integer', 'exists:focus_areas,id'],
                'start_date' => ['required', 'date'],
                'target_date' => ['required', 'date', 'after_or_equal:start_date'],
            ]);
        }

        if ($step === 2) {
            $this->validate([
                'description' => ['required', 'string'],
                'success_criteria' => ['required', 'string'],
            ]);
        }
    }

    public function render()
    {
        return view('livewire.training.training-goal-form')
            ->layout('layouts.app');
    }
}
