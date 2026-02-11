<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MilestoneStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingMilestone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'training_goal_id',
        'title',
        'description',
        'order',
        'status',
        'target_date',
        'completed_at',
        'verified_at',
        'verified_by',
        'completion_notes',
        'evidence_url',
        'evidence_files',
        'points_value',
        'points_awarded',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
        'status' => MilestoneStatus::class,
        'evidence_files' => 'array',
        'points_awarded' => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function goal(): BelongsTo
    {
        return $this->belongsTo(TrainingGoal::class, 'training_goal_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(TrainingCheckin::class, 'milestone_id');
    }

    // ==========================================
    // METHODS
    // ==========================================

    public function markAsCompleted(?string $notes = null, ?string $evidenceUrl = null): void
    {
        $this->update([
            'status' => MilestoneStatus::Completed,
            'completed_at' => now(),
            'completion_notes' => $notes,
            'evidence_url' => $evidenceUrl,
        ]);

        $this->goal->recalculateProgress();
    }

    public function verify(User $verifier): void
    {
        $this->update([
            'status' => MilestoneStatus::Verified,
            'verified_at' => now(),
            'verified_by' => $verifier->id,
        ]);

        $this->goal->recalculateProgress();
    }

    public function skip(): void
    {
        $this->update([
            'status' => MilestoneStatus::Skipped,
        ]);

        $this->goal->recalculateProgress();
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->target_date 
            && $this->target_date->isPast() 
            && !in_array($this->status, [MilestoneStatus::Completed, MilestoneStatus::Verified, MilestoneStatus::Skipped]);
    }
}
