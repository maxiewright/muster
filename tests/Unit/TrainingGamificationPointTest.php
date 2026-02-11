<?php

use App\Enums\TrainingGamificationPoint;

it('has correct number of cases', function () {
    expect(TrainingGamificationPoint::cases())->toHaveCount(15);
});

it('maps labels and point values correctly for goal lifecycle', function () {
    expect(TrainingGamificationPoint::GoalCreated->label())->toBe('Goal Created')
        ->and(TrainingGamificationPoint::GoalCreated->points())->toBe(5)
        ->and(TrainingGamificationPoint::GoalActivated->label())->toBe('Goal Activated')
        ->and(TrainingGamificationPoint::GoalActivated->points())->toBe(10)
        ->and(TrainingGamificationPoint::GoalCompleted->label())->toBe('Goal Completed')
        ->and(TrainingGamificationPoint::GoalCompleted->points())->toBe(50)
        ->and(TrainingGamificationPoint::GoalVerified->label())->toBe('Goal Verified')
        ->and(TrainingGamificationPoint::GoalVerified->points())->toBe(25)
        ->and(TrainingGamificationPoint::GoalEarlyCompletion->label())->toBe('Early Completion Bonus')
        ->and(TrainingGamificationPoint::GoalEarlyCompletion->points())->toBe(20);
});

it('maps labels and point values correctly for milestones and check-ins', function () {
    expect(TrainingGamificationPoint::MilestoneCompleted->label())->toBe('Milestone Completed')
        ->and(TrainingGamificationPoint::MilestoneCompleted->points())->toBe(15)
        ->and(TrainingGamificationPoint::MilestoneVerified->label())->toBe('Milestone Verified')
        ->and(TrainingGamificationPoint::MilestoneVerified->points())->toBe(10)
        ->and(TrainingGamificationPoint::CheckinLogged->label())->toBe('Check-in Logged')
        ->and(TrainingGamificationPoint::CheckinLogged->points())->toBe(5)
        ->and(TrainingGamificationPoint::CheckinWithLearnings->label())->toBe('Shared Learnings')
        ->and(TrainingGamificationPoint::CheckinWithLearnings->points())->toBe(3)
        ->and(TrainingGamificationPoint::CheckinStreakBonus->label())->toBe('Streak Bonus')
        ->and(TrainingGamificationPoint::CheckinStreakBonus->points())->toBe(2);
});

it('maps labels and point values correctly for partner activities', function () {
    expect(TrainingGamificationPoint::PartnerAccepted->label())->toBe('Partner Accepted')
        ->and(TrainingGamificationPoint::PartnerAccepted->points())->toBe(10)
        ->and(TrainingGamificationPoint::PartnerFeedback->label())->toBe('Partner Feedback')
        ->and(TrainingGamificationPoint::PartnerFeedback->points())->toBe(5)
        ->and(TrainingGamificationPoint::PartnerVerification->label())->toBe('Partner Verification')
        ->and(TrainingGamificationPoint::PartnerVerification->points())->toBe(10)
        ->and(TrainingGamificationPoint::PartnerEncouragement->label())->toBe('Partner Encouragement')
        ->and(TrainingGamificationPoint::PartnerEncouragement->points())->toBe(3)
        ->and(TrainingGamificationPoint::HourLogged->label())->toBe('Hour Logged')
        ->and(TrainingGamificationPoint::HourLogged->points())->toBe(2);
});
