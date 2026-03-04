<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PartnerStatus;
use App\Enums\Role;
use App\Enums\TrainingGoalStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, InteractsWithMedia, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'oauth_provider',
        'oauth_id',
        'role',
        'theme',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    public function canAssignTasks(): bool
    {
        $role = $this->role ?? Role::Lead;

        return in_array('assign_tasks', $role->permissions(), true);
    }

    public function isLead(): bool
    {
        return ($this->role ?? Role::Lead) === Role::Lead;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(64)
            ->height(64)
            ->sharpen(10);
        $this->addMediaConversion('avatar')
            ->width(128)
            ->height(128)
            ->sharpen(10);
    }

    /**
     * Profile image URL: uploaded avatar, then Gravatar, then initials placeholder.
     */
    public function profileImageUrl(string $size = 'avatar'): string
    {
        $media = $this->getFirstMedia('avatar');
        if ($media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
            return $media->getUrl($size);
        }
        $hash = md5(strtolower(trim($this->email)));
        $s = $size === 'thumb' ? 64 : 128;

        return "https://www.gravatar.com/avatar/{$hash}?s={$s}&d=mp";
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'user_id');
    }

    public function standups(): HasMany
    {
        return $this->hasMany(Standup::class, 'user_id');
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(UserCheckin::class, 'user_id');
    }

    public function pointLogs(): HasMany
    {
        return $this->hasMany(PointLog::class, 'user_id');
    }

    public function trainingGoals(): HasMany
    {
        return $this->hasMany(TrainingGoal::class);
    }

    public function partnerGoals(): HasMany
    {
        return $this->hasMany(TrainingGoal::class, 'accountability_partner_id');
    }

    public function trainingCheckins(): HasMany
    {
        return $this->hasMany(TrainingCheckin::class);
    }

    public function partnerNotifications(): HasMany
    {
        return $this->hasMany(PartnerNotification::class);
    }

    public function activeTrainingGoals(): HasMany
    {
        return $this->trainingGoals()->where('status', TrainingGoalStatus::Active);
    }

    public function pendingPartnerRequests(): HasMany
    {
        return $this->partnerGoals()
            ->where('accountability_partner_id', $this->id)
            ->where('partner_status', PartnerStatus::Pending);
    }

    public function unreadPartnerNotifications(): HasMany
    {
        return $this->partnerNotifications()->whereNull('read_at');
    }

    protected function getActiveGoalsCountAttribute(): int
    {
        return $this->trainingGoals()->where('status', TrainingGoalStatus::Active)->count();
    }

    protected function getPartnerGoalsCountAttribute(): int
    {
        return $this->partnerGoals()
            ->where('partner_status', PartnerStatus::Accepted)
            ->whereIn('status', [TrainingGoalStatus::Active, TrainingGoalStatus::Completed])
            ->count();
    }

    public function todaysStandup(): ?Standup
    {
        return $this->standups()->whereDate('date', today())->first();
    }

    public function latestStandup(): ?Standup
    {
        return $this->standups()->latest('date')->first();
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class)
            ->withPivot('earned_at');
    }

    public function sentInvitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class, 'invited_by_user_id');
    }

    public function awardPoints(int $points, string $reason, string $type, ?Model $related = null): void
    {
        $this->pointLogs()->create([
            'points' => $points,
            'reason' => $reason,
            'type' => $type,
            'related_type' => $related instanceof \Illuminate\Database\Eloquent\Model ? get_class($related) : null,
            'related_id' => $related?->id,
        ]);

        $this->increment('points', $points);
    }

    public function updateStreak(): void
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Find the last standup before today
        $previousStandup = $this->standups()
            ->whereDate('date', '<', $today)
            ->latest('date')
            ->first();

        if ($previousStandup && $previousStandup->date->toDateString() === $yesterday) {
            // Continuing streak -- use atomic increment to prevent lost updates
            $this->increment('current_streak');
        } else {
            // Streak broken or first check-in -- reset atomically
            $this->forceFill(['current_streak' => 1])->save();
        }

        // Refresh to get the updated value after atomic operation
        $this->refresh();

        // Update longest streak atomically using MAX to avoid read-modify-write race
        // MAX() is compatible with both PostgreSQL and SQLite
        $this->query()
            ->where('id', $this->id)
            ->update([
                'longest_streak' => DB::raw("MAX(longest_streak, {$this->current_streak})"),
            ]);

        // Refresh to reflect the longest_streak update in the model
        $this->refresh();
    }

    public function earnBadge(Badge $badge): bool
    {
        if ($this->badges()->where('badge_id', $badge->id)->exists()) {
            return false;
        }

        $this->badges()->attach($badge->id, ['earned_at' => now()]);

        if ($badge->points_reward > 0) {
            $this->awardPoints($badge->points_reward, "Earned badge: {$badge->name}", 'badge');
        }

        return true;
    }

    protected function rank(): Attribute
    {
        return new Attribute(function (): int|float {
            return User::where('points', '>', $this->points)->count() + 1;
        });
    }
}
