<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'unit_id',
        'user_id',
        'from_user_id',
        'training_goal_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'actioned_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
            'actioned_at' => 'datetime',
        ];
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

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(TrainingGoal::class, 'training_goal_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    #[Scope]
    protected function unread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->whereNull('actioned_at');
    }

    #[Scope]
    protected function inUnit(Builder $query, ?int $unitId): void
    {
        if ($unitId !== null) {
            $query->where('unit_id', $unitId);
        }
    }

    // ==========================================
    // METHODS
    // ==========================================

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsActioned(): void
    {
        $this->update(['actioned_at' => now()]);
    }
}
