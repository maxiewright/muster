<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $event_type_id
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property string|null $color
 * @property bool $is_recurring
 * @property int|null $organization_id
 * @property int|null $unit_id
 */
class Event extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'unit_id',
        'user_id',
        'event_type_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'color',
        'is_recurring',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_recurring' => 'boolean',
        ];
    }

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

    public function type(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
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

        return $this->user_id === $user->id;
    }

    /**
     * Get the color from the event type
     */
    protected function getTypeColorAttribute(): ?string
    {
        return $this->type?->color;
    }

    #[Scope]
    protected function inUnit(Builder $query, ?int $unitId): void
    {
        if ($unitId !== null) {
            $query->where('unit_id', $unitId);
        }
    }
}
