<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
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
 */
class Event extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
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

    public function type(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    /**
     * Get the color from the event type
     */
    public function getTypeColorAttribute(): ?string
    {
        return $this->type?->color;
    }
}
