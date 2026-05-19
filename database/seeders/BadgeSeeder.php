<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            // ==========================================
            // FIRST ACTIONS - Getting Started
            // ==========================================
            [
                'slug' => 'first-muster',
                'name' => 'First Step',
                'description' => 'Completed your first muster check-in',
                'icon' => '🎖️',
                'color' => '#10b981',
                'points_reward' => 25,
            ],
            [
                'slug' => 'first-task',
                'name' => 'Mission Accepted',
                'description' => 'Created your first task',
                'icon' => '📋',
                'color' => '#3b82f6',
                'points_reward' => 10,
            ],
            [
                'slug' => 'first-completion',
                'name' => 'Mission Complete',
                'description' => 'Completed your first task',
                'icon' => '✅',
                'color' => '#22c55e',
                'points_reward' => 15,
            ],

            // ==========================================
            // STREAK BADGES - Consistency & Momentum
            // ==========================================
            [
                'slug' => 'streak-3',
                'name' => 'Steadfast',
                'description' => '3 consecutive days on muster',
                'icon' => '🔥',
                'color' => '#f59e0b',
                'points_reward' => 15,
            ],
            [
                'slug' => 'streak-7',
                'name' => 'Iron Will',
                'description' => '7 consecutive days on muster - one full week',
                'icon' => '⚔️',
                'color' => '#8b5cf6',
                'points_reward' => 50,
            ],
            [
                'slug' => 'streak-14',
                'name' => 'Fortnight Focus',
                'description' => '14 consecutive days on muster',
                'icon' => '🛡️',
                'color' => '#ec4899',
                'points_reward' => 100,
            ],
            [
                'slug' => 'streak-21',
                'name' => 'Built to Last',
                'description' => '21 consecutive days - strong momentum established',
                'icon' => '💪',
                'color' => '#ef4444',
                'points_reward' => 150,
            ],
            [
                'slug' => 'streak-30',
                'name' => 'Sentinel',
                'description' => '30 consecutive days on muster - unwavering dedication',
                'icon' => '🏰',
                'color' => '#eab308',
                'points_reward' => 250,
            ],
            [
                'slug' => 'streak-60',
                'name' => 'Vanguard',
                'description' => '60 consecutive days - leading from the front',
                'icon' => '⭐',
                'color' => '#f97316',
                'points_reward' => 500,
            ],
            [
                'slug' => 'streak-90',
                'name' => 'Ninety-Day Streak',
                'description' => '90 consecutive days - legendary consistency',
                'icon' => '👑',
                'color' => '#fbbf24',
                'points_reward' => 1000,
            ],

            // ==========================================
            // POINTS MILESTONES - Progress Tiers
            // ==========================================
            [
                'slug' => 'points-100',
                'name' => 'Foundation',
                'description' => 'Earned 100 points',
                'icon' => '🔰',
                'color' => '#6b7280',
                'points_reward' => 10,
            ],
            [
                'slug' => 'points-250',
                'name' => 'Building',
                'description' => 'Earned 250 points',
                'icon' => '📍',
                'color' => '#3b82f6',
                'points_reward' => 25,
            ],
            [
                'slug' => 'points-500',
                'name' => 'Developing',
                'description' => 'Earned 500 points',
                'icon' => '🎯',
                'color' => '#6366f1',
                'points_reward' => 50,
            ],
            [
                'slug' => 'points-1000',
                'name' => 'Capable',
                'description' => 'Earned 1,000 points',
                'icon' => '🎖️',
                'color' => '#8b5cf6',
                'points_reward' => 100,
            ],
            [
                'slug' => 'points-2500',
                'name' => 'Proficient',
                'description' => 'Earned 2,500 points',
                'icon' => '🏅',
                'color' => '#a855f7',
                'points_reward' => 150,
            ],
            [
                'slug' => 'points-5000',
                'name' => 'Advanced',
                'description' => 'Earned 5,000 points',
                'icon' => '🎗️',
                'color' => '#d946ef',
                'points_reward' => 250,
            ],
            [
                'slug' => 'points-10000',
                'name' => 'Vanguard',
                'description' => 'Earned 10,000 points',
                'icon' => '⚜️',
                'color' => '#eab308',
                'points_reward' => 500,
            ],

            // ==========================================
            // TASK COMPLETION - Delivery Achievements
            // ==========================================
            [
                'slug' => 'tasks-10',
                'name' => 'Task Starter',
                'description' => 'Completed 10 tasks',
                'icon' => '⚡',
                'color' => '#22c55e',
                'points_reward' => 20,
            ],
            [
                'slug' => 'tasks-25',
                'name' => 'Task Planner',
                'description' => 'Completed 25 tasks',
                'icon' => '🗡️',
                'color' => '#14b8a6',
                'points_reward' => 50,
            ],
            [
                'slug' => 'tasks-50',
                'name' => 'Task Finisher',
                'description' => 'Completed 50 tasks',
                'icon' => '🏹',
                'color' => '#0ea5e9',
                'points_reward' => 100,
            ],
            [
                'slug' => 'tasks-100',
                'name' => 'Delivery Leader',
                'description' => 'Completed 100 tasks',
                'icon' => '🦅',
                'color' => '#6366f1',
                'points_reward' => 200,
            ],
            [
                'slug' => 'tasks-250',
                'name' => 'Execution Expert',
                'description' => 'Completed 250 tasks',
                'icon' => '🎖️',
                'color' => '#8b5cf6',
                'points_reward' => 500,
            ],

            // ==========================================
            // SPECIAL ACHIEVEMENTS - Highlights
            // ==========================================
            [
                'slug' => 'early-bird',
                'name' => 'Early Start',
                'description' => 'Checked in before 6:00 AM, 5 times',
                'icon' => '🌅',
                'color' => '#f97316',
                'points_reward' => 30,
            ],
            [
                'slug' => 'early-bird-10',
                'name' => 'Sunrise Starter',
                'description' => 'Checked in before 6:00 AM, 10 times',
                'icon' => '🌄',
                'color' => '#ea580c',
                'points_reward' => 75,
            ],
            [
                'slug' => 'night-owl',
                'name' => 'Late Push',
                'description' => 'Checked in after 10:00 PM, 5 times',
                'icon' => '🦉',
                'color' => '#4338ca',
                'points_reward' => 30,
            ],
            [
                'slug' => 'weekend-warrior',
                'name' => 'Weekend Momentum',
                'description' => 'Checked in on 5 weekends',
                'icon' => '⚔️',
                'color' => '#dc2626',
                'points_reward' => 50,
            ],
            [
                'slug' => 'blocker-buster',
                'name' => 'Obstacle Solver',
                'description' => 'Reported and resolved 10 blockers',
                'icon' => '💥',
                'color' => '#ef4444',
                'points_reward' => 40,
            ],
            [
                'slug' => 'zero-blockers',
                'name' => 'Clear Path',
                'description' => 'Completed a full week with no blockers',
                'icon' => '🛤️',
                'color' => '#22c55e',
                'points_reward' => 35,
            ],

            // ==========================================
            // TEAMWORK - Collaboration
            // ==========================================
            [
                'slug' => 'battle-buddy',
                'name' => 'Trusted Teammate',
                'description' => 'Helped a teammate overcome a blocker',
                'icon' => '🤝',
                'color' => '#14b8a6',
                'points_reward' => 30,
            ],
            [
                'slug' => 'squad-leader',
                'name' => 'Team Coordinator',
                'description' => 'Assigned 10 tasks to team members',
                'icon' => '📢',
                'color' => '#0891b2',
                'points_reward' => 50,
            ],
            [
                'slug' => 'force-multiplier',
                'name' => 'Momentum Builder',
                'description' => 'Helped teammates complete 25 tasks',
                'icon' => '🚀',
                'color' => '#7c3aed',
                'points_reward' => 100,
            ],
            [
                'slug' => 'unit-cohesion',
                'name' => 'Shared Momentum',
                'description' => 'Full team checked in on the same day, 10 times',
                'icon' => '🎪',
                'color' => '#2563eb',
                'points_reward' => 75,
            ],

            // ==========================================
            // FOCUS & LEARNING - Training Badges
            // ==========================================
            [
                'slug' => 'specialist-laravel',
                'name' => 'Laravel Specialist',
                'description' => 'Focused on Laravel for 10 check-ins',
                'icon' => '🔴',
                'color' => '#ef4444',
                'points_reward' => 40,
            ],
            [
                'slug' => 'specialist-livewire',
                'name' => 'Livewire Specialist',
                'description' => 'Focused on Livewire for 10 check-ins',
                'icon' => '⚡',
                'color' => '#ec4899',
                'points_reward' => 40,
            ],
            [
                'slug' => 'specialist-filament',
                'name' => 'Filament Specialist',
                'description' => 'Focused on Filament for 10 check-ins',
                'icon' => '🟠',
                'color' => '#f97316',
                'points_reward' => 40,
            ],
            [
                'slug' => 'specialist-vue',
                'name' => 'Vue Specialist',
                'description' => 'Focused on Vue.js for 10 check-ins',
                'icon' => '💚',
                'color' => '#22c55e',
                'points_reward' => 40,
            ],
            [
                'slug' => 'polyglot',
                'name' => 'Polyglot',
                'description' => 'Focused on 5 different technologies',
                'icon' => '🌐',
                'color' => '#6366f1',
                'points_reward' => 75,
            ],
            [
                'slug' => 'deep-dive',
                'name' => 'Deep Dive',
                'description' => 'Focused on one technology for 30 consecutive check-ins',
                'icon' => '🤿',
                'color' => '#0ea5e9',
                'points_reward' => 150,
            ],

            // ==========================================
            // MONTHLY ACHIEVEMENTS - Consistency Milestones
            // ==========================================
            [
                'slug' => 'perfect-week',
                'name' => 'Perfect Week',
                'description' => 'Checked in every day for a full week (Mon-Sun)',
                'icon' => '📅',
                'color' => '#8b5cf6',
                'points_reward' => 75,
            ],
            [
                'slug' => 'perfect-month',
                'name' => 'Perfect Month',
                'description' => 'Checked in every workday for a full month',
                'icon' => '🏆',
                'color' => '#eab308',
                'points_reward' => 300,
            ],
            [
                'slug' => 'quarterly-excellence',
                'name' => 'Quarter of Excellence',
                'description' => 'Maintained 90%+ check-in rate for a quarter',
                'icon' => '🎖️',
                'color' => '#f59e0b',
                'points_reward' => 500,
            ],

            // ==========================================
            // PRODUCTIVITY - Execution Efficiency
            // ==========================================
            [
                'slug' => 'task-blitz',
                'name' => 'Blitz',
                'description' => 'Completed 5 tasks in a single day',
                'icon' => '💨',
                'color' => '#06b6d4',
                'points_reward' => 40,
            ],
            [
                'slug' => 'task-surge',
                'name' => 'Surge',
                'description' => 'Completed 10 tasks in a single day',
                'icon' => '🌊',
                'color' => '#0891b2',
                'points_reward' => 100,
            ],
            [
                'slug' => 'no-carry-over',
                'name' => 'Clean Sweep',
                'description' => 'Completed all planned tasks 5 days in a row',
                'icon' => '🧹',
                'color' => '#22c55e',
                'points_reward' => 60,
            ],
            [
                'slug' => 'overachiever',
                'name' => 'Above and Beyond',
                'description' => 'Completed more tasks than planned, 10 times',
                'icon' => '🚀',
                'color' => '#a855f7',
                'points_reward' => 75,
            ],

            // ==========================================
            // RARE & PRESTIGIOUS - Long-Term Recognition
            // ==========================================
            [
                'slug' => 'founding-member',
                'name' => 'Founding Member',
                'description' => 'One of the original Muster team members',
                'icon' => '🏛️',
                'color' => '#d97706',
                'points_reward' => 100,
            ],
            [
                'slug' => 'hundred-days',
                'name' => 'Hundred Check-Ins',
                'description' => 'Completed 100 total check-ins',
                'icon' => '💯',
                'color' => '#7c3aed',
                'points_reward' => 200,
            ],
            [
                'slug' => 'thousand-tasks',
                'name' => 'Thousand Tasks',
                'description' => 'Completed 1,000 tasks',
                'icon' => '🦁',
                'color' => '#dc2626',
                'points_reward' => 1000,
            ],
            [
                'slug' => 'year-service',
                'name' => 'Year of Service',
                'description' => 'Active on Muster for one full year',
                'icon' => '🎗️',
                'color' => '#eab308',
                'points_reward' => 500,
            ],

            // ==========================================
            // TRAINING GOAL BADGES
            // ==========================================
            [
                'slug' => 'first-training-goal',
                'name' => 'Goal Starter',
                'description' => 'Completed your first training goal',
                'icon' => '🎓',
                'color' => '#10b981',
                'points_reward' => 25,
            ],
            [
                'slug' => 'training-veteran',
                'name' => 'Training Veteran',
                'description' => 'Completed 5 training goals',
                'icon' => '📚',
                'color' => '#3b82f6',
                'points_reward' => 50,
            ],
            [
                'slug' => 'training-master',
                'name' => 'Training Master',
                'description' => 'Completed 10 training goals',
                'icon' => '🎖️',
                'color' => '#8b5cf6',
                'points_reward' => 100,
            ],
            [
                'slug' => 'training-legend',
                'name' => 'Training Legend',
                'description' => 'Completed 25 training goals',
                'icon' => '👑',
                'color' => '#eab308',
                'points_reward' => 250,
            ],

            // ==========================================
            // TRAINING TIME BADGES
            // ==========================================
            [
                'slug' => 'training-10-hours',
                'name' => 'Dedicated Learner',
                'description' => 'Logged 10 hours of training',
                'icon' => '⏱️',
                'color' => '#06b6d4',
                'points_reward' => 20,
            ],
            [
                'slug' => 'training-50-hours',
                'name' => 'Committed Student',
                'description' => 'Logged 50 hours of training',
                'icon' => '📖',
                'color' => '#0891b2',
                'points_reward' => 75,
            ],
            [
                'slug' => 'training-centurion',
                'name' => 'Training Century',
                'description' => 'Logged 100 hours of training',
                'icon' => '💯',
                'color' => '#7c3aed',
                'points_reward' => 150,
            ],
            [
                'slug' => 'training-marathon',
                'name' => 'Marathon Learner',
                'description' => 'Logged 500 hours of training',
                'icon' => '🏃',
                'color' => '#dc2626',
                'points_reward' => 500,
            ],

            // ==========================================
            // ACCOUNTABILITY PARTNER BADGES
            // ==========================================
            [
                'slug' => 'first-verification',
                'name' => 'Verifier',
                'description' => 'Verified your first partner achievement',
                'icon' => '✓',
                'color' => '#22c55e',
                'points_reward' => 15,
            ],
            [
                'slug' => 'trusted-partner',
                'name' => 'Trusted Partner',
                'description' => 'Verified 5 partner achievements',
                'icon' => '🤝',
                'color' => '#14b8a6',
                'points_reward' => 40,
            ],
            [
                'slug' => 'accountability-ace',
                'name' => 'Accountability Ace',
                'description' => 'Verified 10 partner achievements',
                'icon' => '⭐',
                'color' => '#f59e0b',
                'points_reward' => 75,
            ],
            [
                'slug' => 'mentor',
                'name' => 'Mentor',
                'description' => 'Verified 25 partner achievements - true leadership',
                'icon' => '🧭',
                'color' => '#8b5cf6',
                'points_reward' => 150,
            ],
            [
                'slug' => 'feedback-champion',
                'name' => 'Feedback Champion',
                'description' => 'Provided feedback on 10 partner check-ins',
                'icon' => '💬',
                'color' => '#ec4899',
                'points_reward' => 50,
            ],

            // ==========================================
            // SPECIAL TRAINING BADGES
            // ==========================================
            [
                'slug' => 'speedrunner',
                'name' => 'Speedrunner',
                'description' => 'Completed a training goal at least 7 days early',
                'icon' => '⚡',
                'color' => '#f97316',
                'points_reward' => 40,
            ],
            [
                'slug' => 'perfectionist',
                'name' => 'Perfectionist',
                'description' => 'Had all milestones verified on a goal',
                'icon' => '💎',
                'color' => '#06b6d4',
                'points_reward' => 35,
            ],
            [
                'slug' => 'consistent-learner',
                'name' => 'Consistent Learner',
                'description' => 'Logged training check-ins for 7 consecutive days',
                'icon' => '📈',
                'color' => '#22c55e',
                'points_reward' => 30,
            ],
            [
                'slug' => 'partnership-complete',
                'name' => 'Partnership Complete',
                'description' => 'Both you and your partner completed goals together',
                'icon' => '🎭',
                'color' => '#8b5cf6',
                'points_reward' => 75,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['slug' => $badge['slug']],
                $badge
            );
        }

        $this->command->info('🏅 '.count($badges).' badges seeded successfully!');
    }
}
