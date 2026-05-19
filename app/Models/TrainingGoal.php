<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PartnerStatus;
use App\Enums\TrainingCategory;
use App\Enums\TrainingGoalStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TrainingGoal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'organization_id',
        'unit_id',
        'user_id',
        'assigned_by_user_id',
        'accountability_partner_id',
        'title',
        'description',
        'success_criteria',
        'category',
        'focus_area_id',
        'start_date',
        'target_date',
        'completed_at',
        'status',
        'partner_status',
        'partner_decline_reason',
        'estimated_hours',
        'base_points_value',
        'is_public',
        'is_unit_directed',
        'accountability_partner_required',
        'accountability_partner_locked',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_date' => 'date',
            'completed_at' => 'datetime',
            'verified_at' => 'datetime',
            'status' => TrainingGoalStatus::class,
            'partner_status' => PartnerStatus::class,
            'category' => TrainingCategory::class,
            'is_public' => 'boolean',
            'is_unit_directed' => 'boolean',
            'accountability_partner_required' => 'boolean',
            'accountability_partner_locked' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TrainingGoal $goal): void {
            if (empty($goal->slug)) {
                $goal->slug = Str::slug($goal->title).'-'.Str::random(8);
            }
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accountability_partner_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function focusArea(): BelongsTo
    {
        return $this->belongsTo(FocusArea::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(TrainingMilestone::class)->orderBy('order');
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(TrainingCheckin::class)->latest();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', TrainingGoalStatus::Active);
    }

    #[Scope]
    protected function forUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    #[Scope]
    protected function asPartner(Builder $query, User $user): void
    {
        $query->where('accountability_partner_id', $user->id)
            ->where('partner_status', PartnerStatus::Accepted);
    }

    #[Scope]
    protected function public(Builder $query): void
    {
        $query->where('is_public', true);
    }

    #[Scope]
    protected function needingPartnerResponse(Builder $query, User $user): void
    {
        $query->where('accountability_partner_id', $user->id)
            ->where('partner_status', PartnerStatus::Pending);
    }

    #[Scope]
    protected function inUnit(Builder $query, ?int $unitId): void
    {
        if ($unitId !== null) {
            $query->where('unit_id', $unitId);
        }
    }

    // ==========================================
    // COMPUTED ATTRIBUTES
    // ==========================================

    protected function getDaysRemainingAttribute(): int
    {
        if ($this->completed_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->target_date, false));
    }

    protected function getDaysTotalAttribute(): int
    {
        return (int) $this->start_date->diffInDays($this->target_date);
    }

    protected function getDaysElapsedAttribute(): int
    {
        $end = $this->completed_at ?? now();

        return max(0, (int) $this->start_date->diffInDays($end));
    }

    protected function getIsOverdueAttribute(): bool
    {
        return ! $this->completed_at && $this->target_date->isPast();
    }

    protected function getLoggedHoursAttribute(): float
    {
        return round($this->logged_minutes / 60, 1);
    }

    protected function getCompletedMilestonesCountAttribute(): int
    {
        return $this->milestones()->whereIn('status', ['completed', 'verified'])->count();
    }

    protected function getTotalMilestonesCountAttribute(): int
    {
        return $this->milestones()->count();
    }

    protected function getHasPartnerAttribute(): bool
    {
        return $this->accountability_partner_id !== null
            && $this->partner_status === PartnerStatus::Accepted;
    }

    // ==========================================
    // METHODS
    // ==========================================

    public function recalculateProgress(): void
    {
        $milestones = $this->milestones;

        if ($milestones->isEmpty()) {
            // No milestones - base on time elapsed vs total time
            $this->progress_percentage = min(100, (int) (($this->getDaysElapsedAttribute() / max(1, $this->getDaysTotalAttribute())) * 100));
        } else {
            // Based on milestone completion
            $completed = $milestones->whereIn('status', ['completed', 'verified'])->count();
            $this->progress_percentage = (int) (($completed / $milestones->count()) * 100);
        }

        $this->save();
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => TrainingGoalStatus::Completed,
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);
    }

    public function verify(User $verifier): void
    {
        $this->status = TrainingGoalStatus::Verified;
        $this->verified_at = now();
        $this->verified_by = $verifier->id;
        $this->save();
    }

    public function canBeEditedBy(User $user): bool
    {
        return $this->belongsToUserContext($user)
            && $this->user_id === $user->id
            && $this->status->canEdit();
    }

    public function canBeVerifiedBy(User $user): bool
    {
        return $this->belongsToUserContext($user)
            && (($this->accountability_partner_id === $user->id && $this->partner_status === PartnerStatus::Accepted)
            || $user->isLead());
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->belongsToUserContext($user) && $this->user_id === $user->id;
    }

    public function isInUnit(?int $unitId): bool
    {
        return $unitId === null || $this->unit_id === null || $this->unit_id === $unitId;
    }

    public function belongsToUserContext(User $user): bool
    {
        $activeUnitId = $user->activeUnitId();

        if ($activeUnitId !== null && $this->unit_id !== null && $this->unit_id !== $activeUnitId) {
            return false;
        }

        return $this->organization_id === null
            || $user->organization_id === null
            || $this->organization_id === $user->organization_id;
    }
}
