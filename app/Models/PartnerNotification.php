<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerNotification extends Model
{
    use HasFactory;
    protected $fillable = [
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

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'actioned_at' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopePending($query)
    {
        return $query->whereNull('actioned_at');
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
