<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamInvitation extends Model
{
    use HasFactory;

    public const KIND_BOOTSTRAP = 'bootstrap';

    public const KIND_TEAM = 'team';

    protected $fillable = [
        'kind',
        'organization_id',
        'unit_id',
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

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->whereNull('accepted_at');
    }

    #[Scope]
    protected function bootstrap(Builder $query): void
    {
        $query->where('kind', self::KIND_BOOTSTRAP);
    }

    #[Scope]
    protected function team(Builder $query): void
    {
        $query->where('kind', self::KIND_TEAM);
    }

    #[Scope]
    protected function inUnit(Builder $query, ?int $unitId): void
    {
        if ($unitId !== null) {
            $query->where('unit_id', $unitId);
        }
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

    public function isBootstrap(): bool
    {
        return $this->kind === self::KIND_BOOTSTRAP;
    }

    public function markAsAccepted(): void
    {
        $this->forceFill(['accepted_at' => now()])->save();
    }
}
