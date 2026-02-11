<?php

use App\Enums\GamificationPoint;

it('maps labels and point values correctly', function () {
    expect(GamificationPoint::Checkin->label())->toBe('Check-in')
        ->and(GamificationPoint::Checkin->points())->toBe(10)
        ->and(GamificationPoint::StreakBonus->label())->toBe('Streak Bonus')
        ->and(GamificationPoint::StreakBonus->points())->toBe(5)
        ->and(GamificationPoint::EarlyBird->label())->toBe('Early Bird')
        ->and(GamificationPoint::EarlyBird->points())->toBe(5)
        ->and(GamificationPoint::BlockerShared->label())->toBe('Blocker Shared')
        ->and(GamificationPoint::BlockerShared->points())->toBe(3)
        ->and(GamificationPoint::HuddleCreated->label())->toBe('Huddle Created')
        ->and(GamificationPoint::HuddleCreated->points())->toBe(5)
        ->and(GamificationPoint::HuddleAttended->label())->toBe('Huddle Attended')
        ->and(GamificationPoint::HuddleAttended->points())->toBe(5);
});
