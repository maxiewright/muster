<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConfidenceLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingCheckin extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'training_goal_id',
        'user_id',
        'milestone_id',
        'progress_update',
        'learnings',
        'blockers',
        'next_steps',
        'minutes_logged',
        'confidence_level',
        'partner_feedback',
        'feedback_by',
        'feedback_at',
        'partner_reaction',
        'points_earned',
    ];

    protected $casts = [
        'feedback_at' => 'datetime',
        'confidence_level' => ConfidenceLevel::class,
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function goal(): BelongsTo
    {
        return $this->belongsTo(TrainingGoal::class, 'training_goal_id');
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

    // ==========================================
    // COMPUTED
    // ==========================================

    public function getHoursLoggedAttribute(): float
    {
        return round($this->minutes_logged / 60, 1);
    }

    public function getHasFeedbackAttribute(): bool
    {
        return !empty($this->partner_feedback);
    }
}
