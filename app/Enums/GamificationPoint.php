<?php

namespace App\Enums;

enum GamificationPoint: string
{
    case Checkin = 'checkin';
    case StreakBonus = 'streak_bonus';       // per day in streak
    case EarlyBird = 'early_bird';           // check-in before 9 AM
    case BlockerShared = 'blocker_shared';
    case HuddleCreated = 'huddle_created';
    case HuddleAttended = 'huddle_attended';

    public function label(): string
    {
        return match ($this) {
            self::Checkin => 'Check-in',
            self::StreakBonus => 'Streak Bonus',
            self::EarlyBird => 'Early Bird',
            self::BlockerShared => 'Blocker Shared',
            self::HuddleCreated => 'Huddle Created',
            self::HuddleAttended => 'Huddle Attended',
        };
    }

    public function points(): int
    {
        return match ($this) {
            self::Checkin => 10,
            self::StreakBonus => 5,
            self::EarlyBird => 5,
            self::BlockerShared => 3,
            self::HuddleCreated => 5,
            self::HuddleAttended => 5,
        };
    }
}
