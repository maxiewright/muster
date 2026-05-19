<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\GamificationPoint;
use App\Events\BadgeEarned;
use App\Events\MusterCreated;
use App\Events\PointsEarned;
use App\Models\Badge;
use App\Models\Muster;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    public function processCheckin(User $user, Muster $muster): array
    {
        return DB::transaction(function () use ($user, $muster): array {
            // Refresh user with pessimistic lock to prevent concurrent corruption
            $user = User::query()->lockForUpdate()->findOrFail($user->id);

            $pointsEarned = [];

            // Base check-in points
            $user->awardPoints(GamificationPoint::Checkin->points(), 'Daily check-in', 'checkin', $muster);
            $pointsEarned[] = ['points' => GamificationPoint::Checkin->points(), 'reason' => 'Daily check-in'];

            // Update streak
            $user->updateStreak();

            // Streak bonus
            if ($user->current_streak > 1) {
                $streakBonus = min($user->current_streak * GamificationPoint::StreakBonus->points(), 50); // Cap at 50
                $user->awardPoints($streakBonus, "{$user->current_streak} day streak!", 'streak_bonus', $muster);
                $pointsEarned[] = ['points' => $streakBonus, 'reason' => "{$user->current_streak} day streak!"];
            }

            // Early bird bonus (before 9 AM)
            if (now()->hour < 9) {
                $user->awardPoints(GamificationPoint::EarlyBird->points(), 'Early bird bonus', 'early_bird', $muster);
                $pointsEarned[] = ['points' => GamificationPoint::EarlyBird->points(), 'reason' => 'Early bird bonus'];
            }

            // Blocker shared bonus
            if (! empty($muster->blockers)) {
                $user->awardPoints(GamificationPoint::BlockerShared->points(), 'Shared a blocker', 'blocker', $muster);
                $pointsEarned[] = ['points' => GamificationPoint::BlockerShared->points(), 'reason' => 'Shared a blocker'];
            }

            $earnedBadges = $this->checkBadges($user);

            broadcast(new MusterCreated($muster))->toOthers();
            broadcast(new PointsEarned($user->fresh(), array_sum(array_column($pointsEarned, 'points'))))->toOthers();

            return [
                'points' => $pointsEarned,
                'badges' => $earnedBadges,
            ];
        });
    }

    public function checkBadges(User $user): array
    {
        $earnedBadges = [];

        // Streak badges (aligned with BadgeSeeder)
        $streakBadges = [
            3 => 'streak-3',
            7 => 'streak-7',
            14 => 'streak-14',
            21 => 'streak-21',
            30 => 'streak-30',
            60 => 'streak-60',
            90 => 'streak-90',
        ];

        // Points milestones (aligned with BadgeSeeder)
        $pointBadges = [
            100 => 'points-100',
            250 => 'points-250',
            500 => 'points-500',
            1000 => 'points-1000',
            2500 => 'points-2500',
            5000 => 'points-5000',
            10000 => 'points-10000',
        ];

        // Pre-load all relevant badges in a single query to avoid N+1 (was up to 15 queries).
        $allSlugs = array_merge(
            array_values($streakBadges),
            array_values($pointBadges),
            ['first-muster', 'hundred-days']
        );

        /** @var Collection<string, Badge> $badges */
        $badges = Badge::whereIn('slug', $allSlugs)->get()->keyBy('slug');

        // First Check-in
        if ($user->musters()->count() === 1) {
            $badge = $badges->get('first-muster');
            if ($badge && $user->earnBadge($badge)) {
                $earnedBadges[] = $badge;
                broadcast(new BadgeEarned($user, $badge))->toOthers();
            }
        }

        // Streak badges
        foreach ($streakBadges as $days => $slug) {
            if ($user->current_streak >= $days) {
                $badge = $badges->get($slug);
                if ($badge && $user->earnBadge($badge)) {
                    $earnedBadges[] = $badge;
                    broadcast(new BadgeEarned($user, $badge))->toOthers();
                }
            }
        }

        // Points milestone badges
        foreach ($pointBadges as $points => $slug) {
            if ($user->points >= $points) {
                $badge = $badges->get($slug);
                if ($badge && $user->earnBadge($badge)) {
                    $earnedBadges[] = $badge;
                    broadcast(new BadgeEarned($user, $badge))->toOthers();
                }
            }
        }

        // Total check-ins milestone badges
        $totalCheckins = $user->musters()->count();
        if ($totalCheckins >= 100) {
            $badge = $badges->get('hundred-days');
            if ($badge && $user->earnBadge($badge)) {
                $earnedBadges[] = $badge;
                broadcast(new BadgeEarned($user, $badge))->toOthers();
            }
        }

        return $earnedBadges;
    }
}
