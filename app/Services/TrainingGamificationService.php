<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PartnerStatus;
use App\Enums\TrainingGamificationPoint;
use App\Events\BadgeEarned;
use App\Events\TrainingCheckinLogged;
use App\Models\Badge;
use App\Models\PartnerNotification;
use App\Models\TrainingCheckin;
use App\Models\TrainingGoal;
use App\Models\TrainingMilestone;
use App\Models\User;
use Illuminate\Support\Collection;

class TrainingGamificationService
{
    // ==========================================
    // GOAL EVENTS
    // ==========================================

    public function onGoalCreated(TrainingGoal $goal): array
    {
        $points = [];
        $user = $goal->user;

        $user->awardPoints(TrainingGamificationPoint::GoalCreated->points(), 'Created training goal', 'training_goal', $goal);
        $points[] = ['points' => TrainingGamificationPoint::GoalCreated->points(), 'reason' => '📋 Created training goal'];

        $this->checkTrainingBadges($user);

        return $points;
    }

    public function onGoalActivated(TrainingGoal $goal): array
    {
        $points = [];
        $user = $goal->user;
        $partner = $goal->partner;

        $user->awardPoints(TrainingGamificationPoint::GoalActivated->points(), 'Training goal activated', 'training_goal', $goal);
        $points[] = ['points' => TrainingGamificationPoint::GoalActivated->points(), 'reason' => '🎯 Goal activated with partner'];

        if ($partner) {
            $partner->awardPoints(TrainingGamificationPoint::PartnerAccepted->points(), 'Accepted accountability partner role', 'partner_activity', $goal);
        }

        return $points;
    }

    public function onGoalCompleted(TrainingGoal $goal): array
    {
        $points = [];
        $user = $goal->user;

        $user->awardPoints(TrainingGamificationPoint::GoalCompleted->points(), 'Completed training goal', 'training_goal', $goal);
        $points[] = ['points' => TrainingGamificationPoint::GoalCompleted->points(), 'reason' => '✅ Completed training goal'];

        if ($goal->completed_at && $goal->completed_at->lt($goal->target_date)) {
            $daysEarly = (int) $goal->completed_at->diffInDays($goal->target_date);
            $earlyBonus = min(TrainingGamificationPoint::GoalEarlyCompletion->points(), $daysEarly * 5);
            $user->awardPoints($earlyBonus, "Completed {$daysEarly} days early", 'training_bonus', $goal);
            $points[] = ['points' => $earlyBonus, 'reason' => "🚀 Completed {$daysEarly} days early"];
        }

        $this->checkTrainingBadges($user);

        return $points;
    }

    public function onGoalVerified(TrainingGoal $goal, User $verifier): array
    {
        $points = [];
        $user = $goal->user;

        $user->awardPoints(TrainingGamificationPoint::GoalVerified->points(), 'Goal verified by partner', 'training_goal', $goal);
        $points[] = ['points' => TrainingGamificationPoint::GoalVerified->points(), 'reason' => '🏅 Goal verified by partner'];

        $verifier->awardPoints(TrainingGamificationPoint::PartnerVerification->points(), 'Verified partner\'s goal', 'partner_activity', $goal);

        $this->checkTrainingBadges($user);
        $this->checkPartnerBadges($verifier);

        return $points;
    }

    // ==========================================
    // MILESTONE EVENTS
    // ==========================================

    public function onMilestoneCompleted(TrainingMilestone $milestone): array
    {
        $points = [];
        $user = $milestone->goal->user;

        $milestonePoints = $milestone->points_value ?: TrainingGamificationPoint::MilestoneCompleted->points();
        $user->awardPoints($milestonePoints, "Completed milestone: {$milestone->title}", 'milestone', $milestone);
        $points[] = ['points' => $milestonePoints, 'reason' => "🎯 Milestone: {$milestone->title}"];

        $milestone->update(['points_awarded' => true]);

        return $points;
    }

    public function onMilestoneVerified(TrainingMilestone $milestone, User $verifier): array
    {
        $points = [];
        $user = $milestone->goal->user;

        $user->awardPoints(TrainingGamificationPoint::MilestoneVerified->points(), 'Milestone verified', 'milestone', $milestone);
        $points[] = ['points' => TrainingGamificationPoint::MilestoneVerified->points(), 'reason' => '✓ Milestone verified'];

        $verifier->awardPoints(TrainingGamificationPoint::PartnerVerification->points(), 'Verified milestone', 'partner_activity', $milestone);

        return $points;
    }

    // ==========================================
    // CHECK-IN EVENTS
    // ==========================================

    public function onCheckinLogged(TrainingCheckin $checkin): array
    {
        $points = [];
        $user = $checkin->user;
        $goal = $checkin->goal;

        $user->awardPoints(TrainingGamificationPoint::CheckinLogged->points(), 'Logged training progress', 'training_checkin', $checkin);
        $points[] = ['points' => TrainingGamificationPoint::CheckinLogged->points(), 'reason' => '📝 Logged training progress'];

        if (! empty($checkin->learnings)) {
            $user->awardPoints(TrainingGamificationPoint::CheckinWithLearnings->points(), 'Shared learnings', 'training_checkin', $checkin);
            $points[] = ['points' => TrainingGamificationPoint::CheckinWithLearnings->points(), 'reason' => '💡 Shared learnings'];
        }

        if ($checkin->minutes_logged >= 60) {
            $hours = (int) floor($checkin->minutes_logged / 60);
            $timePoints = $hours * TrainingGamificationPoint::HourLogged->points();
            $user->awardPoints($timePoints, "{$hours}h of training logged", 'training_time', $checkin);
            $points[] = ['points' => $timePoints, 'reason' => "⏱️ {$hours}h of training"];
        }

        $goal->increment('logged_minutes', $checkin->minutes_logged);

        $this->notifyPartnerOfCheckin($checkin);

        return $points;
    }

    public function onPartnerFeedback(TrainingCheckin $checkin, User $partner): array
    {
        $points = [];

        $partner->awardPoints(TrainingGamificationPoint::PartnerFeedback->points(), 'Provided partner feedback', 'partner_activity', $checkin);
        $points[] = ['points' => TrainingGamificationPoint::PartnerFeedback->points(), 'reason' => '💬 Provided feedback'];

        $this->checkPartnerBadges($partner);

        return $points;
    }

    // ==========================================
    // BADGE CHECKS
    // ==========================================

    /**
     * @return array<int, Badge>
     */
    protected function checkTrainingBadges(User $user): array
    {
        $goalBadgeSlugs = [
            1 => 'first-training-goal',
            5 => 'training-veteran',
            10 => 'training-master',
            25 => 'training-legend',
        ];
        $hourBadgeSlugs = [
            10 => 'training-10-hours',
            50 => 'training-50-hours',
            100 => 'training-centurion',
            500 => 'training-marathon',
        ];

        /** @var Collection<string, Badge> $badges */
        $badges = Badge::query()
            ->whereIn('slug', array_merge(array_values($goalBadgeSlugs), array_values($hourBadgeSlugs)))
            ->get()
            ->keyBy('slug');

        $earnedBadges = [];

        $completedGoals = $user->trainingGoals()
            ->whereIn('status', ['completed', 'verified'])
            ->count();

        foreach ($goalBadgeSlugs as $count => $slug) {
            if ($completedGoals >= $count) {
                $earnedBadges = array_merge($earnedBadges, $this->awardBadgeIfExists($user, $badges->get($slug)));
            }
        }

        $totalMinutes = $user->trainingGoals()->sum('logged_minutes');
        $totalHours = $totalMinutes / 60;

        foreach ($hourBadgeSlugs as $hours => $slug) {
            if ($totalHours >= $hours) {
                $earnedBadges = array_merge($earnedBadges, $this->awardBadgeIfExists($user, $badges->get($slug)));
            }
        }

        return $earnedBadges;
    }

    /**
     * @return array<int, Badge>
     */
    protected function checkPartnerBadges(User $user): array
    {
        $partnerBadgeSlugs = [
            1 => 'first-verification',
            5 => 'trusted-partner',
            10 => 'accountability-ace',
            25 => 'mentor',
        ];

        /** @var Collection<string, Badge> $badges */
        $badges = Badge::query()
            ->whereIn('slug', array_merge(array_values($partnerBadgeSlugs), ['feedback-champion']))
            ->get()
            ->keyBy('slug');

        $earnedBadges = [];

        $verificationsCount = TrainingGoal::query()->where('verified_by', $user->id)->count()
            + TrainingMilestone::query()->where('verified_by', $user->id)->count();

        foreach ($partnerBadgeSlugs as $count => $slug) {
            if ($verificationsCount >= $count) {
                $earnedBadges = array_merge($earnedBadges, $this->awardBadgeIfExists($user, $badges->get($slug)));
            }
        }

        $feedbackCount = TrainingCheckin::query()->where('feedback_by', $user->id)->count();

        if ($feedbackCount >= 10) {
            $earnedBadges = array_merge($earnedBadges, $this->awardBadgeIfExists($user, $badges->get('feedback-champion')));
        }

        return $earnedBadges;
    }

    /**
     * @return array<int, Badge>
     */
    protected function awardBadgeIfExists(User $user, ?Badge $badge): array
    {
        if ($badge && $user->earnBadge($badge)) {
            broadcast(new BadgeEarned($user, $badge))->toOthers();

            return [$badge];
        }

        return [];
    }

    // ==========================================
    // NOTIFICATIONS
    // ==========================================

    protected function notifyPartnerOfCheckin(TrainingCheckin $checkin): void
    {
        $goal = $checkin->goal;
        $partner = $goal->partner;

        if (! $partner || $goal->partner_status !== PartnerStatus::Accepted) {
            return;
        }

        PartnerNotification::create([
            'user_id' => $partner->id,
            'from_user_id' => $checkin->user_id,
            'training_goal_id' => $goal->id,
            'type' => 'checkin_logged',
            'title' => "{$checkin->user->name} logged training progress",
            'message' => $checkin->progress_update,
            'data' => [
                'checkin_id' => $checkin->id,
                'goal_title' => $goal->title,
                'minutes_logged' => $checkin->minutes_logged,
                'confidence' => $checkin->confidence_level?->value,
            ],
        ]);

        TrainingCheckinLogged::dispatch($checkin, $partner);
    }
}
