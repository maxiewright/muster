<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasSlugFromName;
use Database\Factories\MissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\SlugOptions;

class Mission extends Model
{
    /** @use HasFactory<MissionFactory> */
    use HasFactory;

    use HasSlugFromName;

    protected $fillable = [
        'organization_id',
        'unit_id',
        'mission_commander_user_id',
        'name',
        'slug',
        'description',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function commander(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mission_commander_user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(MissionMembership::class);
    }

    public function currentMemberships(): HasMany
    {
        return $this->memberships()->whereNull('ended_at');
    }

    public function currentMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mission_memberships')
            ->withPivot(['membership_type', 'added_by_user_id', 'removed_by_user_id', 'started_at', 'ended_at'])
            ->withTimestamps()
            ->wherePivotNull('ended_at');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function ensureMember(User $user, string $membershipType, ?int $addedByUserId = null): MissionMembership
    {
        /** @var MissionMembership $membership */
        $membership = $this->memberships()->firstOrCreate(
            [
                'user_id' => $user->id,
                'ended_at' => null,
            ],
            [
                'membership_type' => $membershipType,
                'added_by_user_id' => $addedByUserId,
                'started_at' => now(),
            ],
        );

        if ($membership->membership_type !== $membershipType) {
            $membership->forceFill(['membership_type' => $membershipType])->save();
        }

        return $membership;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public static function defaultForUnit(Unit $unit, User $commander): self
    {
        /** @var self $mission */
        $mission = self::query()->firstOrCreate(
            [
                'unit_id' => $unit->id,
                'slug' => 'unit-operations',
            ],
            [
                'organization_id' => $unit->organization_id,
                'mission_commander_user_id' => $commander->id,
                'name' => 'Unit Operations',
                'description' => 'Default mission for active unit action capture.',
            ],
        );

        $mission->ensureMember($commander, 'permanent', $commander->id);

        return $mission;
    }
}
