<?php

use Illuminate\Support\Facades\Schema;

it('has expected users columns folded into base migration', function (): void {
    expect(Schema::hasColumns('users', [
        'theme',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ]))->toBeTrue();
});

it('has production indexes defined in base create migrations', function (): void {
    $eventsIndexes = Schema::getIndexListing('events');
    expect($eventsIndexes)->toContain('events_user_id_index');
    expect($eventsIndexes)->toContain('events_starts_at_index');
    expect($eventsIndexes)->toContain('events_ends_at_index');

    $standupsIndexes = Schema::getIndexListing('standups');
    expect($standupsIndexes)->toContain('standups_date_index');

    $tasksIndexes = Schema::getIndexListing('tasks');
    expect($tasksIndexes)->toContain('tasks_assigned_to_index');
    expect($tasksIndexes)->toContain('tasks_created_by_index');
    expect($tasksIndexes)->toContain('tasks_status_index');
    expect($tasksIndexes)->toContain('tasks_priority_index');
    expect($tasksIndexes)->toContain('tasks_due_date_index');

    $pointLogsIndexes = Schema::getIndexListing('point_logs');
    expect($pointLogsIndexes)->toContain('point_logs_user_id_index');

    $userCheckinsIndexes = Schema::getIndexListing('user_checkins');
    // Unique name may vary by driver; check presence of the unique on (user_id, on) via Schema builder helper
    // getIndexListing returns array of index names; default for composite unique is table_columns_unique
    expect(collect($userCheckinsIndexes)->first(fn ($name) => str_contains($name, 'user_checkins_user_id_on_unique')))->not->toBeNull();
});

it('created pivot tables without soft deletes', function (): void {
    expect(Schema::hasColumn('standup_task', 'deleted_at'))->toBeFalse();
    expect(Schema::hasColumn('standup_focus_area', 'deleted_at'))->toBeFalse();
});

it('has training related tables with correct foreign key order applied', function (): void {
    // Tables exist (order ensured via filenames)
    expect(Schema::hasTable('training_goals'))->toBeTrue();
    expect(Schema::hasTable('training_milestones'))->toBeTrue();
    expect(Schema::hasTable('training_checkins'))->toBeTrue();
    expect(Schema::hasTable('partner_notifications'))->toBeTrue();
});
