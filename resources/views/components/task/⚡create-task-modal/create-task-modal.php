<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Events\TaskAssigned;
use App\Events\TaskCreated;
use App\Models\Mission;
use App\Models\Task;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public ?int $taskId = null;

    public ?string $presetStatus = null;

    public ?int $presetAssignee = null;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:2000')]
    public string $description = '';

    #[Validate('nullable|integer')]
    public ?int $mission_id = null;

    #[Validate('required|string')]
    public string $status = 'todo';

    #[Validate('required|string')]
    public string $priority = 'medium';

    #[Validate('nullable|exists:users,id')]
    public ?int $assigned_to = null;

    #[Validate('array')]
    public array $assigned_members = [];

    #[Validate('nullable|date')]
    public ?string $due_date = null;

    #[Validate('nullable|string|max:2000')]
    public string $notes = '';

    public function mount(?int $taskId = null, ?string $presetStatus = null, ?int $presetAssignee = null): void
    {
        $activeUnitId = auth()->user()?->activeUnitId();
        $this->taskId = $taskId;
        $this->presetStatus = $presetStatus;
        $this->presetAssignee = $presetAssignee;

        if ($taskId) {
            $task = Task::query()->inUnit($activeUnitId)->findOrFail($taskId);
            $this->title = $task->title;
            $this->description = $task->description ?? '';
            $this->mission_id = $task->mission_id;
            $this->status = $task->status->value;
            $this->priority = $task->priority->value;
            $this->assigned_to = $task->assigned_to;
            $this->assigned_members = $task->assignedMembers()->pluck('users.id')->map(fn (int $userId): string => (string) $userId)->all();
            $this->due_date = $task->due_date?->format('Y-m-d');
            $this->notes = $task->notes ?? '';
        } else {
            $activeUnit = auth()->user()?->activeUnit();

            if ($activeUnit instanceof Unit) {
                $this->mission_id = Mission::defaultForUnit($activeUnit, auth()->user())->id;
            }

            // Apply presets for new tasks
            if ($presetStatus) {
                $this->status = $presetStatus;
            }
            if ($presetAssignee) {
                $this->assigned_to = $presetAssignee;
            }
        }
    }

    #[Computed]
    public function task(): ?Task
    {
        return $this->taskId ? Task::query()->inUnit(auth()->user()?->activeUnitId())->find($this->taskId) : null;
    }

    #[Computed]
    public function missions()
    {
        return Mission::query()
            ->where('unit_id', auth()->user()?->activeUnitId())
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function teamMembers()
    {
        $activeUnit = auth()->user()?->activeUnit();

        if ($activeUnit instanceof Unit) {
            return $activeUnit->users()->orderBy('name')->get();
        }

        return User::orderBy('name')->get();
    }

    #[Computed]
    public function statuses(): array
    {
        return TaskStatus::cases();
    }

    #[Computed]
    public function priorities(): array
    {
        return TaskPriority::cases();
    }

    #[Computed]
    public function canAssign(): bool
    {
        return auth()->user()->canAssignTasks();
    }

    #[Computed]
    public function canDelete(): bool
    {
        if (! $this->task) {
            return false;
        }

        $user = auth()->user();
        if ($user->isLead()) {
            return true;
        }

        return $this->task->created_by === $user->id;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'mission_id' => [
                'nullable',
                'integer',
                Rule::exists('missions', 'id')->where('unit_id', auth()->user()?->activeUnitId()),
            ],
            'status' => ['required', 'string'],
            'priority' => ['required', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'assigned_members' => ['array'],
            'assigned_members.*' => ['integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();
        $activeUnit = $user->activeUnit();
        $mission = null;

        if ($activeUnit instanceof Unit) {
            $mission = $this->mission_id !== null
                ? Mission::query()->where('unit_id', $activeUnit->id)->findOrFail($this->mission_id)
                : Mission::defaultForUnit($activeUnit, $user);
        }

        if ($this->taskId) {
            $task = Task::query()->inUnit($user->activeUnitId())->findOrFail($this->taskId);
            $this->authorize('update', $task);
        } else {
            $this->authorize('create', Task::class);
        }

        $assignedMemberIds = collect($this->assigned_members)
            ->map(fn (mixed $userId): int => (int) $userId)
            ->filter()
            ->unique()
            ->values();

        if ($this->assigned_to !== null) {
            $assignedMemberIds = $assignedMemberIds
                ->push($this->assigned_to)
                ->unique()
                ->values();
        }

        $actionLeadUserId = $this->assigned_to ?? $assignedMemberIds->first();

        // Check if user can assign to others
        if ($actionLeadUserId && $actionLeadUserId !== $user->id && ! $user->canAssignTasks()) {
            $this->addError('assigned_to', 'You do not have permission to assign tasks to others.');

            return;
        }

        $data = [
            'organization_id' => $user->activeUnit()?->organization_id,
            'unit_id' => $user->activeUnitId(),
            'mission_id' => $mission?->id,
            'title' => $this->title,
            'description' => $this->description ?: null,
            'status' => $this->status,
            'priority' => $this->priority,
            'assigned_to' => $actionLeadUserId,
            'action_lead_user_id' => $actionLeadUserId,
            'due_date' => $this->due_date ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->taskId) {
            $task = Task::query()->inUnit($user->activeUnitId())->findOrFail($this->taskId);
            $task->update($data);
        } else {
            $data['created_by'] = $user->id;
            $task = Task::create($data);
            event(new TaskCreated($task));
            if ($task->assigned_to !== null && $task->assigned_to !== $task->created_by) {
                event(new TaskAssigned($task->fresh(['assignee', 'creator'])));
            }
        }

        $task->assignedMembers()->sync($assignedMemberIds->all());

        if ($mission instanceof Mission) {
            $assignedUsers = User::query()
                ->whereIn('id', $assignedMemberIds->all())
                ->get();

            foreach ($assignedUsers as $assignedUser) {
                $existingMembership = $mission->currentMemberships()
                    ->where('user_id', $assignedUser->id)
                    ->exists();

                $mission->ensureMember(
                    $assignedUser,
                    $existingMembership ? 'permanent' : 'temporary',
                    $user->id,
                );
            }
        }

        $this->dispatch('task-saved');
    }

    public function delete(): void
    {
        if (! $this->task) {
            return;
        }

        $this->authorize('delete', $this->task);

        $this->task->delete();
        $this->dispatch('task-deleted');
    }

    public function render(): Factory|View
    {
        return view('components.task.⚡create-task-modal.create-task-modal');
    }
};
