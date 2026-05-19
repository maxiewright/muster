<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasSlugFromName;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\SlugOptions;

class Task extends Model
{
    use HasFactory;
    use HasSlugFromName;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'unit_id',
        'mission_id',
        'assigned_to',
        'action_lead_user_id',
        'created_by',
        'parent_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_date' => 'date',
            'notes' => 'array',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actionLead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_lead_user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id')->orderBy('id');
    }

    public function isSubtask(): bool
    {
        return $this->parent_id !== null;
    }

    public function subtasksCompletedCount(): int
    {
        return $this->subtasks()->where('status', TaskStatus::Completed)->count();
    }

    public function subtasksTotalCount(): int
    {
        return $this->subtasks()->count();
    }

    public function musters(): BelongsToMany
    {
        return $this->belongsToMany(Muster::class, 'muster_task', 'task_id', 'muster_id')
            ->withPivot('status', 'notes')
            ->withTimestamps();
    }

    public function musterTasks(): HasMany
    {
        return $this->hasMany(MusterTask::class, 'task_id');
    }

    public function assignedMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignments')
            ->withTimestamps();
    }

    // Scopes
    #[Scope]
    protected function assignedTo(Builder $query, User $user): void
    {
        $query->where('assigned_to', $user->id);
    }

    #[Scope]
    protected function createdBy(Builder $query, User $user): void
    {
        $query->where('created_by', $user->id);
    }

    #[Scope]
    protected function unassigned(Builder $query): void
    {
        $query->whereNull('assigned_to');
    }

    #[Scope]
    protected function rootTasks(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    #[Scope]
    protected function subtasksOnly(Builder $query): void
    {
        $query->whereNotNull('parent_id');
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNotIn('status', [TaskStatus::Completed, TaskStatus::Backlog]);
    }

    #[Scope]
    protected function overdue(Builder $query): void
    {
        $query->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->whereNot('status', TaskStatus::Completed);
    }

    #[Scope]
    protected function dueToday(Builder $query): void
    {
        $query->whereDate('due_date', today());
    }

    #[Scope]
    protected function dueThisWeek(Builder $query): void
    {
        $query->whereBetween('due_date', [today(), today()->endOfWeek()]);
    }

    // Helpers
    public function isOverdue(): bool
    {
        return $this->due_date &&
            $this->due_date->lt(today()) &&
            $this->status !== TaskStatus::Completed;
    }

    public function isDueToday(): bool
    {
        return $this->due_date?->isToday() ?? false;
    }

    public function isDueSoon(): bool
    {
        return $this->due_date &&
            $this->due_date->between(today(), today()->addDays(3)) &&
            $this->status !== TaskStatus::Completed;
    }

    public function isAssignedTo(User $user): bool
    {
        if ($this->assigned_to === $user->id || $this->action_lead_user_id === $user->id) {
            return true;
        }

        return $this->assignedMembers()->whereKey($user->id)->exists();
    }

    public function canBeEditedBy(User $user): bool
    {
        $activeUnitId = $user->activeUnitId();

        if ($activeUnitId !== null && $this->unit_id !== $activeUnitId) {
            return false;
        }

        if ($user->isLead()) {
            return true;
        }
        if ($this->created_by === $user->id) {
            return true;
        }

        return $this->assigned_to === $user->id;
    }

    #[Scope]
    protected function inUnit(Builder $query, ?int $unitId): void
    {
        if ($unitId !== null) {
            $query->where('unit_id', $unitId);
        }
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }
}
