<?php

namespace App\Models;

use App\Enums\Mood;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Standup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
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

    public function focusAreas(): BelongsToMany
    {
        return $this->belongsToMany(
            FocusArea::class,
            'standup_focus_area',
            'standup_id',
            'focus_area_id'
        )->withTimestamps();
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'standup_task')
            ->withPivot('status', 'notes')
            ->withTimestamps();
    }

    public function standupTasks(): HasMany
    {
        return $this->hasMany(StandUpTask::class, 'standup_id');
    }

    /**
     * Get mood options as array
     *
     * @return array<string, string>
     */
    public static function getMoods(): array
    {
        return collect(Mood::cases())
            ->mapWithKeys(fn (Mood $mood) => [$mood->value => $mood->label()])
            ->toArray();
    }

    /**
     * Get focus areas (placeholder for backward compatibility)
     *
     * @return array<string, string>
     */
    public static function getFocusAreas(): array
    {
        return FocusArea::pluck('name', 'id')->toArray();
    }
}
