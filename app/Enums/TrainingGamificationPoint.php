<?php

declare(strict_types=1);

namespace App\Enums;

enum TrainingGamificationPoint: string
{
    // Goal lifecycle
    case GoalCreated = 'goal_created';
    case GoalActivated = 'goal_activated';
    case GoalCompleted = 'goal_completed';
    case GoalVerified = 'goal_verified';
    case GoalEarlyCompletion = 'goal_early_completion';

    // Milestones
    case MilestoneCompleted = 'milestone_completed';
    case MilestoneVerified = 'milestone_verified';

    // Check-ins
    case CheckinLogged = 'checkin_logged';
    case CheckinWithLearnings = 'checkin_with_learnings';
    case CheckinStreakBonus = 'checkin_streak_bonus';

    // Partner activities
    case PartnerAccepted = 'partner_accepted';
    case PartnerFeedback = 'partner_feedback';
    case PartnerVerification = 'partner_verification';
    case PartnerEncouragement = 'partner_encouragement';

    // Time-based
    case HourLogged = 'hour_logged';

    public function label(): string
    {
        return match ($this) {
            self::GoalCreated => 'Goal Created',
            self::GoalActivated => 'Goal Activated',
            self::GoalCompleted => 'Goal Completed',
            self::GoalVerified => 'Goal Verified',
            self::GoalEarlyCompletion => 'Early Completion Bonus',
            self::MilestoneCompleted => 'Milestone Completed',
            self::MilestoneVerified => 'Milestone Verified',
            self::CheckinLogged => 'Check-in Logged',
            self::CheckinWithLearnings => 'Shared Learnings',
            self::CheckinStreakBonus => 'Streak Bonus',
            self::PartnerAccepted => 'Partner Accepted',
            self::PartnerFeedback => 'Partner Feedback',
            self::PartnerVerification => 'Partner Verification',
            self::PartnerEncouragement => 'Partner Encouragement',
            self::HourLogged => 'Hour Logged',
        };
    }

    public function points(): int
    {
        return match ($this) {
            self::GoalCreated => 5,
            self::GoalActivated => 10,
            self::GoalCompleted => 50,
            self::GoalVerified => 25,
            self::GoalEarlyCompletion => 20,
            self::MilestoneCompleted => 15,
            self::MilestoneVerified => 10,
            self::CheckinLogged => 5,
            self::CheckinWithLearnings => 3,
            self::CheckinStreakBonus => 2,
            self::PartnerAccepted => 10,
            self::PartnerFeedback => 5,
            self::PartnerVerification => 10,
            self::PartnerEncouragement => 3,
            self::HourLogged => 2,
        };
    }
}
