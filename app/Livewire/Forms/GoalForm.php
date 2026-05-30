<?php

namespace App\Livewire\Forms;

use App\Models\TrainingGoal;
use App\Models\Unit;
use Illuminate\Validation\Rule;
use Livewire\Form;

class GoalForm extends Form
{
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

    public function setGoal(TrainingGoal $goal): void
    {
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
            ->map(fn ($milestone): array => [
                'title' => $milestone->title,
                'target_date' => $milestone->target_date?->toDateString(),
            ])
            ->values()
            ->all();

        $this->milestones = $milestones !== [] ? $milestones : [['title' => '', 'target_date' => null]];
    }

    public function rules(): array
    {
        $activeUnit = auth()->user()?->activeUnit();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'success_criteria' => ['required', 'string'],
            'category' => ['required', 'string'],
            'focus_area_id' => ['required', 'integer', 'exists:focus_areas,id'],
            'start_date' => ['required', 'date'],
            'target_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_public' => ['boolean'],
            'milestones' => ['array'],
            'milestones.*.title' => ['nullable', 'string', 'max:255'],
            'milestones.*.target_date' => ['nullable', 'date'],
        ];

        if ($activeUnit instanceof Unit) {
            $rules['accountability_partner_id'] = [
                'nullable',
                'integer',
                Rule::exists('unit_memberships', 'user_id')
                    ->where('unit_id', $activeUnit->id),
            ];
        }

        return $rules;
    }
}
