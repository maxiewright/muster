<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MilestoneStatus;
use App\Enums\Mood;
use App\Enums\MusterTaskStatus;
use App\Enums\PartnerStatus;
use App\Enums\Role;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\TrainingCategory;
use App\Enums\TrainingGoalStatus;
use App\Enums\UnitMembershipRole;
use App\Models\Badge;
use App\Models\FocusArea;
use App\Models\Muster;
use App\Models\Organization;
use App\Models\Task;
use App\Models\TrainingGoal;
use App\Models\TrainingMilestone;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Seeds a TTR demo organization with one unit, three users (Commander,
     * Lead, Member), a week of musters per user, plus an in-flight training
     * goal owned by the Member with the Commander as accountability partner.
     *
     * Idempotent: re-running wipes only the demo records (matched by org slug
     * and demo email pattern) before rebuilding them.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $organization = Organization::firstOrCreate(
                ['slug' => 'ttr-demo'],
                ['name' => 'Trinidad and Tobago Regiment'],
            );

            $unit = Unit::firstOrCreate(
                ['organization_id' => $organization->id, 'slug' => '1-engineer-battalion'],
                ['name' => '1st Engineer Battalion'],
            );

            $this->wipeExistingDemoData($organization, $unit);

            [$commander, $lead, $member] = $this->createUsers($organization);

            $this->attachToUnit($unit, [
                [$commander, UnitMembershipRole::Commander],
                [$lead, UnitMembershipRole::Lead],
                [$member, UnitMembershipRole::Member],
            ]);

            $this->seedTasksAndMusters($organization, $unit, [$commander, $lead, $member]);
            $this->seedTrainingGoal($organization, $unit, $member, $commander);
            $this->seedGamification($member);
        });
    }

    private function wipeExistingDemoData(Organization $organization, Unit $unit): void
    {
        User::query()
            ->whereIn('email', ['commander@ttr.demo', 'lead@ttr.demo', 'member@ttr.demo'])
            ->get()
            ->each(fn (User $user) => $user->delete());

        // Defensive: cascade should have cleared these, but soft-deleted musters
        // on the unit would survive a user purge.
        Muster::query()->where('unit_id', $unit->id)->forceDelete();
        Task::query()->where('unit_id', $unit->id)->forceDelete();
        TrainingGoal::query()->where('unit_id', $unit->id)->forceDelete();
    }

    /**
     * @return array{0: User, 1: User, 2: User}
     */
    private function createUsers(Organization $organization): array
    {
        $commander = User::create([
            'name' => 'Maj Marcus Charles',
            'email' => 'commander@ttr.demo',
            'password' => Hash::make('password'),
            'role' => Role::Lead->value,
            'organization_id' => $organization->id,
            'email_verified_at' => now(),
        ]);

        $lead = User::create([
            'name' => 'Capt Diana Edwards',
            'email' => 'lead@ttr.demo',
            'password' => Hash::make('password'),
            'role' => Role::Lead->value,
            'organization_id' => $organization->id,
            'email_verified_at' => now(),
        ]);

        $member = User::create([
            'name' => 'Cpl Aaron Joseph',
            'email' => 'member@ttr.demo',
            'password' => Hash::make('password'),
            'role' => Role::Member->value,
            'organization_id' => $organization->id,
            'email_verified_at' => now(),
        ]);

        return [$commander, $lead, $member];
    }

    /**
     * @param  array<int, array{0: User, 1: UnitMembershipRole}>  $assignments
     */
    private function attachToUnit(Unit $unit, array $assignments): void
    {
        foreach ($assignments as [$user, $role]) {
            UnitMembership::create([
                'user_id' => $user->id,
                'unit_id' => $unit->id,
                'role' => $role,
            ]);
        }
    }

    /**
     * @param  array<int, User>  $users
     */
    private function seedTasksAndMusters(Organization $organization, Unit $unit, array $users): void
    {
        $focusAreaIds = FocusArea::query()->pluck('id')->all();

        foreach ($users as $user) {
            $tasks = $this->seedTaskBacklogFor($organization, $unit, $user);

            for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
                $this->seedMusterFor($organization, $unit, $user, $tasks, $focusAreaIds, $daysAgo);
            }
        }
    }

    /**
     * @return array<int, Task>
     */
    private function seedTaskBacklogFor(Organization $organization, Unit $unit, User $user): array
    {
        $titles = [
            'Confirm range allocation for next field exercise',
            'Submit weekly logistics report to HQ',
            'Inspect engineer plant equipment serviceability',
            'Coordinate transport for live-fire training package',
            'Brief section on updated SOPs for vehicle recovery',
            'Review medical fitness returns for the unit',
        ];

        $priorities = [TaskPriority::Medium, TaskPriority::High, TaskPriority::Medium, TaskPriority::Low, TaskPriority::High, TaskPriority::Medium];
        $statuses = [TaskStatus::Todo, TaskStatus::InProgress, TaskStatus::Completed, TaskStatus::Todo, TaskStatus::InProgress, TaskStatus::Todo];

        $tasks = [];
        foreach ($titles as $index => $title) {
            $tasks[] = Task::create([
                'organization_id' => $organization->id,
                'unit_id' => $unit->id,
                'title' => $title,
                'assigned_to' => $user->id,
                'created_by' => $user->id,
                'status' => $statuses[$index],
                'priority' => $priorities[$index],
                'due_date' => now()->addDays(($index * 2) + 1)->toDateString(),
            ]);
        }

        return $tasks;
    }

    /**
     * @param  array<int, Task>  $tasks
     * @param  array<int, int>  $focusAreaIds
     */
    private function seedMusterFor(Organization $organization, Unit $unit, User $user, array $tasks, array $focusAreaIds, int $daysAgo): void
    {
        $moods = [Mood::Steady, Mood::Strong, Mood::Firing, Mood::Steady, Mood::Struggling, Mood::Strong, Mood::Firing];
        $blockerSamples = [
            null,
            null,
            'Awaiting confirmation from MT on vehicle availability.',
            null,
            'Range clearance still pending from RHQ.',
            null,
            null,
        ];

        $muster = Muster::create([
            'organization_id' => $organization->id,
            'unit_id' => $unit->id,
            'user_id' => $user->id,
            'date' => Carbon::today()->subDays($daysAgo)->toDateString(),
            'mood' => $moods[$daysAgo],
            'blockers' => $blockerSamples[$daysAgo],
        ]);

        // Attach 2 focus areas per muster (varies by day for visual variety).
        if ($focusAreaIds !== []) {
            $picked = array_slice($focusAreaIds, $daysAgo % max(count($focusAreaIds) - 1, 1), 2);
            $muster->focusAreas()->sync($picked);
        }

        // Plan three tasks per day from the backlog; oldest musters mark them completed.
        $planned = array_slice($tasks, 0, 3);
        foreach ($planned as $offset => $task) {
            $status = $daysAgo >= 4
                ? MusterTaskStatus::Completed
                : ($offset === 0 ? MusterTaskStatus::Ongoing : MusterTaskStatus::Planned);

            $muster->tasks()->attach($task->id, [
                'status' => $status->value,
                'notes' => null,
            ]);
        }
    }

    private function seedTrainingGoal(Organization $organization, Unit $unit, User $owner, User $partner): void
    {
        $focusArea = FocusArea::query()->first();

        $goal = TrainingGoal::create([
            'organization_id' => $organization->id,
            'unit_id' => $unit->id,
            'user_id' => $owner->id,
            'accountability_partner_id' => $partner->id,
            'title' => 'Qualify on Combat First Responder (CFR) — Level 2',
            'description' => 'Complete the Level-2 CFR package including tactical casualty care, evacuation drills, and assessed scenarios.',
            'success_criteria' => 'Pass the assessed scenario with an instructor sign-off and submit the CFR certificate.',
            'category' => TrainingCategory::Certification->value,
            'focus_area_id' => $focusArea?->id,
            'start_date' => now()->subWeeks(3)->toDateString(),
            'target_date' => now()->addWeeks(2)->toDateString(),
            'status' => TrainingGoalStatus::Active->value,
            'partner_status' => PartnerStatus::Accepted->value,
            'is_public' => true,
            'progress_percentage' => 50,
            'estimated_hours' => 40,
            'logged_minutes' => 720,
        ]);

        $milestones = [
            ['Complete e-learning module 1: anatomy refresher', -14, MilestoneStatus::Verified],
            ['Pass classroom assessment on tourniquet application', -7, MilestoneStatus::Completed],
            ['Demonstrate casualty evacuation under load', 3, MilestoneStatus::InProgress],
            ['Pass full mission-profile assessed scenario', 10, MilestoneStatus::Pending],
        ];

        foreach ($milestones as $order => [$title, $offsetDays, $status]) {
            TrainingMilestone::create([
                'training_goal_id' => $goal->id,
                'title' => $title,
                'order' => $order + 1,
                'status' => $status->value,
                'target_date' => now()->addDays($offsetDays)->toDateString(),
                'completed_at' => in_array($status, [MilestoneStatus::Completed, MilestoneStatus::Verified], true)
                    ? now()->addDays($offsetDays)
                    : null,
            ]);
        }
    }

    private function seedGamification(User $member): void
    {
        $member->forceFill([
            'points' => 420,
            'current_streak' => 7,
            'longest_streak' => 14,
        ])->save();

        $badgeSlugs = ['first-muster', 'streak-3', 'streak-7'];
        $badges = Badge::query()->whereIn('slug', $badgeSlugs)->get();

        foreach ($badges as $badge) {
            DB::table('badge_user')->updateOrInsert(
                ['user_id' => $member->id, 'badge_id' => $badge->id],
                ['earned_at' => now()],
            );
        }
    }
}
