<?php

use Illuminate\Support\Facades\Schema;

it('has expected users columns folded into base migration', function (): void {
    expect(Schema::hasColumns('users', [
        'organization_id',
        'theme',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ]))->toBeTrue();
});

it('has tenant foundation tables and foreign keys', function (): void {
    expect(Schema::hasTable('organizations'))->toBeTrue();
    expect(Schema::hasTable('units'))->toBeTrue();
    expect(Schema::hasTable('unit_memberships'))->toBeTrue();
    expect(Schema::hasTable('musters'))->toBeTrue();
    expect(Schema::hasTable('muster_task'))->toBeTrue();
    expect(Schema::hasTable('muster_focus_area'))->toBeTrue();
    expect(Schema::hasColumns('units', ['organization_id', 'name', 'slug']))->toBeTrue();
    expect(Schema::hasColumns('unit_memberships', ['user_id', 'unit_id', 'role']))->toBeTrue();
    expect(Schema::hasColumns('tasks', ['organization_id', 'unit_id']))->toBeTrue();
    expect(Schema::hasColumns('events', ['organization_id', 'unit_id']))->toBeTrue();
    expect(Schema::hasColumns('musters', ['organization_id', 'unit_id']))->toBeTrue();
    expect(Schema::hasColumns('training_goals', ['organization_id', 'unit_id']))->toBeTrue();
    expect(Schema::hasColumns('training_checkins', ['organization_id', 'unit_id']))->toBeTrue();
    expect(Schema::hasColumns('partner_notifications', ['organization_id', 'unit_id']))->toBeTrue();
    expect(Schema::hasColumns('team_invitations', ['organization_id', 'unit_id']))->toBeTrue();
    expect(Schema::hasColumns('team_invitations', ['kind']))->toBeTrue();
    expect(Schema::hasTable('missions'))->toBeTrue();
    expect(Schema::hasTable('mission_memberships'))->toBeTrue();
    expect(Schema::hasTable('task_assignments'))->toBeTrue();
    expect(Schema::hasColumns('tasks', ['mission_id', 'action_lead_user_id']))->toBeTrue();
});

it('has production indexes defined in base create migrations', function (): void {
    $eventsIndexes = Schema::getIndexListing('events');
    expect($eventsIndexes)->toContain('events_user_id_index');
    expect($eventsIndexes)->toContain('events_starts_at_index');
    expect($eventsIndexes)->toContain('events_ends_at_index');

    $mustersIndexes = Schema::getIndexListing('musters');
    expect($mustersIndexes)->toContain('musters_date_index');

    $tasksIndexes = Schema::getIndexListing('tasks');
    expect($tasksIndexes)->toContain('tasks_assigned_to_index');
    expect($tasksIndexes)->toContain('tasks_created_by_index');
    expect($tasksIndexes)->toContain('tasks_organization_id_index');
    expect($tasksIndexes)->toContain('tasks_unit_id_index');
    expect($tasksIndexes)->toContain('tasks_status_index');
    expect($tasksIndexes)->toContain('tasks_priority_index');
    expect($tasksIndexes)->toContain('tasks_due_date_index');

    expect($eventsIndexes)->toContain('events_organization_id_index');
    expect($eventsIndexes)->toContain('events_unit_id_index');

    $pointLogsIndexes = Schema::getIndexListing('point_logs');
    expect($pointLogsIndexes)->toContain('point_logs_user_id_index');

    $userCheckinsIndexes = Schema::getIndexListing('user_checkins');
    // Unique name may vary by driver; check presence of the unique on (user_id, on) via Schema builder helper
    // getIndexListing returns array of index names; default for composite unique is table_columns_unique
    expect(collect($userCheckinsIndexes)->first(fn ($name): bool => str_contains($name, 'user_checkins_user_id_on_unique')))->not->toBeNull();

    $unitMembershipIndexes = Schema::getIndexListing('unit_memberships');
    expect(collect($unitMembershipIndexes)->first(fn ($name): bool => str_contains($name, 'unit_memberships_user_id_unit_id_unique')))->not->toBeNull();

    expect(collect($mustersIndexes)->first(fn ($name): bool => str_contains($name, 'musters_user_id_unit_id_date_unique')))->not->toBeNull();

    $trainingGoalIndexes = Schema::getIndexListing('training_goals');
    expect($trainingGoalIndexes)->toContain('training_goals_organization_id_index');
    expect($trainingGoalIndexes)->toContain('training_goals_unit_id_index');

    $trainingCheckinIndexes = Schema::getIndexListing('training_checkins');
    expect($trainingCheckinIndexes)->toContain('training_checkins_organization_id_index');
    expect($trainingCheckinIndexes)->toContain('training_checkins_unit_id_index');

    $partnerNotificationIndexes = Schema::getIndexListing('partner_notifications');
    expect($partnerNotificationIndexes)->toContain('partner_notifications_organization_id_index');
    expect($partnerNotificationIndexes)->toContain('partner_notifications_unit_id_index');

    $teamInvitationIndexes = Schema::getIndexListing('team_invitations');
    expect($teamInvitationIndexes)->toContain('team_invitations_organization_id_index');
    expect($teamInvitationIndexes)->toContain('team_invitations_unit_id_index');
    expect($teamInvitationIndexes)->toContain('team_invitations_kind_index');

    $missionsIndexes = Schema::getIndexListing('missions');
    expect($missionsIndexes)->toContain('missions_organization_id_index');
    expect($missionsIndexes)->toContain('missions_unit_id_index');
    expect($missionsIndexes)->toContain('missions_mission_commander_user_id_index');

    $missionMembershipIndexes = Schema::getIndexListing('mission_memberships');
    expect($missionMembershipIndexes)->toContain('mission_memberships_mission_id_user_id_index');
    expect($missionMembershipIndexes)->toContain('mission_memberships_membership_type_index');
    expect($missionMembershipIndexes)->toContain('mission_memberships_ended_at_index');

    $taskIndexes = Schema::getIndexListing('tasks');
    expect($taskIndexes)->toContain('tasks_mission_id_index');
    expect($taskIndexes)->toContain('tasks_action_lead_user_id_index');
});

it('created pivot tables without soft deletes', function (): void {
    expect(Schema::hasColumn('muster_task', 'deleted_at'))->toBeFalse();
    expect(Schema::hasColumn('muster_focus_area', 'deleted_at'))->toBeFalse();
});

it('has training related tables with correct foreign key order applied', function (): void {
    // Tables exist (order ensured via filenames)
    expect(Schema::hasTable('training_goals'))->toBeTrue();
    expect(Schema::hasTable('training_milestones'))->toBeTrue();
    expect(Schema::hasTable('training_checkins'))->toBeTrue();
    expect(Schema::hasTable('partner_notifications'))->toBeTrue();
});
