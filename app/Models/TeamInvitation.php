<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'invited_by_user_id',
        'email',
        'role',
        'token',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function scopePending($query)
    {
        return $query->whereNull('accepted_at');
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function hasExpired(): bool
    {
        return $this->expires_at instanceof CarbonInterface && $this->expires_at->isPast();
    }

    public function hasBeenAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function markAsAccepted(): void
    {
        $this->forceFill(['accepted_at' => now()])->save();
    }
}
