<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConfidenceLevel;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingCheckin extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'unit_id',
        'training_goal_id',
        'user_id',
        'milestone_id',
        'progress_update',
        'learnings',
        'blockers',
        'next_steps',
        'minutes_logged',
        'confidence_level',
    ];

    protected function casts(): array
    {
        return [
            'feedback_at' => 'datetime',
            'confidence_level' => ConfidenceLevel::class,
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function goal(): BelongsTo
    {
        return $this->belongsTo(TrainingGoal::class, 'training_goal_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(TrainingMilestone::class, 'milestone_id');
    }

    public function feedbackProvider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'feedback_by');
    }

    #[Scope]
    protected function inUnit(Builder $query, ?int $unitId): void
    {
        if ($unitId !== null) {
            $query->where('unit_id', $unitId);
        }
    }

    // ==========================================
    // COMPUTED
    // ==========================================

    protected function getHoursLoggedAttribute(): float
    {
        return round($this->minutes_logged / 60, 1);
    }

    protected function getHasFeedbackAttribute(): bool
    {
        return ! empty($this->partner_feedback);
    }
}
