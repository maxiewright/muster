<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Enums\PartnerStatus;
use App\Enums\TrainingCategory;
use App\Enums\TrainingGoalStatus;
use App\Models\FocusArea;
use App\Models\TrainingGoal;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TrainingAssignmentManager extends Component
{
    public string $title = '';

    public string $description = '';

    public string $success_criteria = '';

    public ?string $category = null;

    public ?int $focus_area_id = null;

    public ?string $start_date = null;

    public ?string $target_date = null;

    public bool $assign_to_all_members = false;

    /** @var array<int, int|string> */
    public array $selected_member_ids = [];

    /** @var array<int, int|string> */
    public array $excluded_member_ids = [];

    public string $partner_policy = 'optional';

    public ?int $accountability_partner_id = null;

    public function mount(): void
    {
        abort_unless(Auth::user()?->canAssignUnitTraining(Auth::user()?->activeUnit()), 403);

        $this->start_date = now()->toDateString();
        $this->target_date = now()->addMonth()->toDateString();
    }

    #[Computed]
    public function focusAreas()
    {
        return FocusArea::query()->orderBy('name')->get();
    }

    #[Computed]
    public function categories(): array
    {
        return TrainingCategory::cases();
    }

    #[Computed]
    public function unitMembers()
    {
        $activeUnit = Auth::user()?->activeUnit();

        if (! $activeUnit instanceof Unit) {
            return collect();
        }

        return $activeUnit->users()
            ->where('users.id', '!=', Auth::id())
            ->orderBy('users.name')
            ->get();
    }

    public function save(): void
    {
        $user = Auth::user();
        $activeUnit = $user?->activeUnit();

        abort_unless($user?->canAssignUnitTraining($activeUnit) && $activeUnit instanceof Unit, 403);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'success_criteria' => ['required', 'string'],
            'category' => ['required', 'string'],
            'focus_area_id' => ['required', 'integer', 'exists:focus_areas,id'],
            'start_date' => ['required', 'date'],
            'target_date' => ['required', 'date', 'after_or_equal:start_date'],
            'assign_to_all_members' => ['boolean'],
            'selected_member_ids' => ['array'],
            'selected_member_ids.*' => [
                'integer',
                Rule::exists('unit_memberships', 'user_id')->where('unit_id', $activeUnit->id),
            ],
            'excluded_member_ids' => ['array'],
            'excluded_member_ids.*' => [
                'integer',
                Rule::exists('unit_memberships', 'user_id')->where('unit_id', $activeUnit->id),
            ],
            'partner_policy' => ['required', Rule::in(['optional', 'member_required', 'commander_locked', 'commander_changeable'])],
            'accountability_partner_id' => [
                'nullable',
                'integer',
                Rule::exists('unit_memberships', 'user_id')->where('unit_id', $activeUnit->id),
            ],
        ]);

        if (in_array($this->partner_policy, ['commander_locked', 'commander_changeable'], true) && $this->accountability_partner_id === null) {
            $this->addError('accountability_partner_id', 'Choose the accountability partner to assign.');

            return;
        }

        $targetMemberIds = $this->assign_to_all_members
            ? $this->unitMembers()
                ->pluck('id')
                ->reject(fn (int $memberId): bool => in_array($memberId, array_map('intval', $this->excluded_member_ids), true))
                ->values()
            : collect(array_map('intval', $this->selected_member_ids))->filter()->unique()->values();

        if ($targetMemberIds->isEmpty()) {
            $this->addError('selected_member_ids', 'Choose at least one unit member for this assignment.');

            return;
        }

        foreach ($targetMemberIds as $memberId) {
            TrainingGoal::query()->create([
                'organization_id' => $activeUnit->organization_id,
                'unit_id' => $activeUnit->id,
                'user_id' => $memberId,
                'assigned_by_user_id' => $user->id,
                'accountability_partner_id' => $this->usesAssignedPartner() ? $this->accountability_partner_id : null,
                'title' => $this->title,
                'description' => $this->description,
                'success_criteria' => $this->success_criteria,
                'category' => $this->category,
                'focus_area_id' => $this->focus_area_id,
                'start_date' => $this->start_date,
                'target_date' => $this->target_date,
                'status' => TrainingGoalStatus::Active,
                'partner_status' => $this->usesAssignedPartner() ? PartnerStatus::Accepted : PartnerStatus::None,
                'is_public' => false,
                'is_unit_directed' => true,
                'accountability_partner_required' => $this->partnerPolicyRequiresPartner(),
                'accountability_partner_locked' => $this->partner_policy === 'commander_locked',
            ]);
        }

        session()->flash('status', 'Training assignments created.');

        $this->redirectRoute('training.dashboard', navigate: true);
    }

    private function partnerPolicyRequiresPartner(): bool
    {
        return $this->partner_policy !== 'optional';
    }

    private function usesAssignedPartner(): bool
    {
        return in_array($this->partner_policy, ['commander_locked', 'commander_changeable'], true);
    }

    public function render()
    {
        return view('livewire.training.training-assignment-manager')
            ->layout('layouts.app');
    }
}
