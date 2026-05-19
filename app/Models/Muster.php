<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Mood;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Muster extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'musters';

    protected $fillable = [
        'organization_id',
        'unit_id',
        'user_id',
        'date',
        'blockers',
        'mood',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'mood' => Mood::class,
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

    public function focusAreas(): BelongsToMany
    {
        return $this->belongsToMany(
            FocusArea::class,
            'muster_focus_area',
            'muster_id',
            'focus_area_id'
        )->withTimestamps();
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'muster_task', 'muster_id', 'task_id')
            ->withPivot('status', 'notes')
            ->withTimestamps();
    }

    public function musterTasks(): HasMany
    {
        return $this->hasMany(MusterTask::class, 'muster_id');
    }

    /**
     * @return array<string, string>
     */
    public static function getMoods(): array
    {
        return collect(Mood::cases())
            ->mapWithKeys(fn (Mood $mood): array => [$mood->value => $mood->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function getFocusAreas(): array
    {
        return FocusArea::pluck('name', 'id')->toArray();
    }

    #[Scope]
    protected function inUnit(Builder $query, ?int $unitId): void
    {
        if ($unitId !== null) {
            $query->where('unit_id', $unitId);
        }
    }

    public function isInUnit(?int $unitId): bool
    {
        return $unitId === null || $this->unit_id === null || $this->unit_id === $unitId;
    }
}
