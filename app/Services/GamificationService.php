<?php

namespace App\Services;

use App\Enums\GamificationPoint;
use App\Events\BadgeEarned;
use App\Events\PointsEarned;
use App\Events\StandupCreated;
use App\Models\Badge;
use App\Models\Standup;
use App\Models\User;

class GamificationService
{
    public function processCheckin(User $user, Standup $standup): array
    {
        $pointsEarned = [];

        // Base check-in points
        $user->awardPoints(GamificationPoint::Checkin->points(), 'Daily check-in', 'checkin', $standup);
        $pointsEarned[] = ['points' => GamificationPoint::Checkin->points(), 'reason' => 'Daily check-in'];

        // Update streak
        $user->updateStreak();

        // Streak bonus
        if ($user->current_streak > 1) {
            $streakBonus = min($user->current_streak * GamificationPoint::StreakBonus->points(), 50); // Cap at 50
            $user->awardPoints($streakBonus, "{$user->current_streak} day streak!", 'streak_bonus', $standup);
            $pointsEarned[] = ['points' => $streakBonus, 'reason' => "{$user->current_streak} day streak!"];
        }

        // Early bird bonus (before 9 AM)
        if (now()->hour < 9) {
            $user->awardPoints(GamificationPoint::EarlyBird->points(), 'Early bird bonus', 'early_bird', $standup);
            $pointsEarned[] = ['points' => GamificationPoint::EarlyBird->points(), 'reason' => 'Early bird bonus'];
        }

        // Blocker shared bonus
        if (! empty($standup->blockers)) {
            $user->awardPoints(GamificationPoint::BlockerShared->points(), 'Shared a blocker', 'blocker', $standup);
            $pointsEarned[] = ['points' => GamificationPoint::BlockerShared->points(), 'reason' => 'Shared a blocker'];
        }

        $earnedBadges = $this->checkBadges($user);

        broadcast(new StandupCreated($standup))->toOthers();
        broadcast(new PointsEarned($user->fresh(), array_sum(array_column($pointsEarned, 'points'))))->toOthers();

        return [
            'points' => $pointsEarned,
            'badges' => $earnedBadges,
        ];
    }

    public function checkBadges(User $user): array
    {
        $earnedBadges = [];

        // First Check-in
        if ($user->standups()->count() === 1) {
            $badge = Badge::where('slug', 'first-muster')->first();
            if ($badge && $user->earnBadge($badge)) {
                $earnedBadges[] = $badge;
                broadcast(new BadgeEarned($user, $badge))->toOthers();
            }
        }

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

        foreach ($streakBadges as $days => $slug) {
            if ($user->current_streak >= $days) {
                $badge = Badge::where('slug', $slug)->first();
                if ($badge && $user->earnBadge($badge)) {
                    $earnedBadges[] = $badge;
                    broadcast(new BadgeEarned($user, $badge))->toOthers();
                }
            }
        }

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

        foreach ($pointBadges as $points => $slug) {
            if ($user->points >= $points) {
                $badge = Badge::where('slug', $slug)->first();
                if ($badge && $user->earnBadge($badge)) {
                    $earnedBadges[] = $badge;
                    broadcast(new BadgeEarned($user, $badge))->toOthers();
                }
            }
        }

        // Total check-ins milestone badges
        $totalCheckins = $user->standups()->count();
        if ($totalCheckins >= 100) {
            $badge = Badge::where('slug', 'hundred-days')->first();
            if ($badge && $user->earnBadge($badge)) {
                $earnedBadges[] = $badge;
                broadcast(new BadgeEarned($user, $badge))->toOthers();
            }
        }

        return $earnedBadges;
    }
}
