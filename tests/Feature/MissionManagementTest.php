<?php

declare(strict_types=1);

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Mission;
use App\Models\MissionMembership;
use App\Models\Organization;
use App\Models\Task;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Livewire\Livewire;

function attachMissionManagementUserToUnit(User $user, Organization $organization, Unit $unit, string $role = 'member'): void
{
    $user->forceFill(['organization_id' => $organization->id])->save();

    UnitMembership::query()->updateOrCreate(
        [
            'user_id' => $user->id,
            'unit_id' => $unit->id,
        ],
        [
            'role' => $role,
        ],
    );
}

test('organization leads can create missions with a permanent roster', function (): void {
    $organization = Organization::factory()->create(['name' => 'Regiment']);
    $unit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $lead = User::factory()->lead()->create(['organization_id' => $organization->id]);
    $operator = User::factory()->create(['organization_id' => $organization->id]);

    attachMissionManagementUserToUnit($lead, $organization, $unit, 'owner');
    attachMissionManagementUserToUnit($operator, $organization, $unit);

    $this->actingAs($lead)
        ->withSession(['active_unit_id' => $unit->id])
        ->post(route('missions.store'), [
            'name' => 'Harbor Clearance',
            'description' => 'Establish mission command and readiness.',
            'mission_commander_user_id' => $lead->id,
            'roster_user_ids' => [$lead->id, $operator->id],
        ])
        ->assertRedirect(route('missions.index'));

    $mission = Mission::query()->where('name', 'Harbor Clearance')->first();

    expect($mission)->not->toBeNull();
    expect($mission?->organization_id)->toBe($organization->id);
    expect($mission?->unit_id)->toBe($unit->id);
    expect($mission?->mission_commander_user_id)->toBe($lead->id);
    expect(MissionMembership::query()
        ->where('mission_id', $mission?->id)
        ->where('user_id', $operator->id)
        ->where('membership_type', 'permanent')
        ->whereNull('ended_at')
        ->exists())->toBeTrue();
});

test('missions page only lists missions from the active unit', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $lead = User::factory()->lead()->create(['organization_id' => $organization->id]);

    attachMissionManagementUserToUnit($lead, $organization, $alphaUnit, 'owner');
    attachMissionManagementUserToUnit($lead, $organization, $bravoUnit, 'owner');

    Mission::query()->create([
        'organization_id' => $organization->id,
        'unit_id' => $alphaUnit->id,
        'mission_commander_user_id' => $lead->id,
        'name' => 'Alpha Mission',
        'slug' => 'alpha-mission',
    ]);

    Mission::query()->create([
        'organization_id' => $organization->id,
        'unit_id' => $bravoUnit->id,
        'mission_commander_user_id' => $lead->id,
        'name' => 'Bravo Mission',
        'slug' => 'bravo-mission',
    ]);

    $this->actingAs($lead)
        ->withSession(['active_unit_id' => $alphaUnit->id])
        ->get(route('missions.index'))
        ->assertOk()
        ->assertSee('Alpha Mission')
        ->assertDontSee('Bravo Mission');
});

test('unit members can view missions without mission creation controls', function (): void {
    $organization = Organization::factory()->create();
    $unit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $member = User::factory()->create(['organization_id' => $organization->id]);
    $commander = User::factory()->lead()->create(['organization_id' => $organization->id]);

    attachMissionManagementUserToUnit($member, $organization, $unit);
    attachMissionManagementUserToUnit($commander, $organization, $unit, 'owner');

    Mission::query()->create([
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'mission_commander_user_id' => $commander->id,
        'name' => 'Member Visible Mission',
        'slug' => 'member-visible-mission',
    ]);

    $this->actingAs($member)
        ->withSession(['active_unit_id' => $unit->id])
        ->get(route('missions.index'))
        ->assertOk()
        ->assertSee('Member Visible Mission')
        ->assertDontSee('Create Mission');
});

test('creating an action with multiple assignees adds non-roster members as temporary mission members', function (): void {
    $organization = Organization::factory()->create();
    $unit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $lead = User::factory()->lead()->create(['organization_id' => $organization->id]);
    $actionLead = User::factory()->create(['organization_id' => $organization->id]);
    $temporaryAssignee = User::factory()->create(['organization_id' => $organization->id]);

    attachMissionManagementUserToUnit($lead, $organization, $unit, 'owner');
    attachMissionManagementUserToUnit($actionLead, $organization, $unit);
    attachMissionManagementUserToUnit($temporaryAssignee, $organization, $unit);

    $mission = Mission::query()->create([
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'mission_commander_user_id' => $lead->id,
        'name' => 'Rapid Response',
        'slug' => 'rapid-response',
    ]);

    MissionMembership::query()->create([
        'mission_id' => $mission->id,
        'user_id' => $lead->id,
        'membership_type' => 'permanent',
        'added_by_user_id' => $lead->id,
        'started_at' => now(),
    ]);

    $this->actingAs($lead)->withSession(['active_unit_id' => $unit->id]);

    Livewire::actingAs($lead)
        ->test('task.create-task-modal')
        ->set('mission_id', $mission->id)
        ->set('title', 'Secure the perimeter')
        ->set('status', TaskStatus::Todo->value)
        ->set('priority', TaskPriority::Medium->value)
        ->set('assigned_to', $actionLead->id)
        ->set('assigned_members', [$actionLead->id, $temporaryAssignee->id])
        ->call('save')
        ->assertDispatched('task-saved');

    $task = Task::query()->where('title', 'Secure the perimeter')->first();

    expect($task)->not->toBeNull();
    expect($task?->mission_id)->toBe($mission->id);
    expect($task?->action_lead_user_id)->toBe($actionLead->id);
    expect($task?->assignedMembers()->pluck('users.id')->all())->toBe([$actionLead->id, $temporaryAssignee->id]);
    expect(MissionMembership::query()
        ->where('mission_id', $mission->id)
        ->where('user_id', $temporaryAssignee->id)
        ->where('membership_type', 'temporary')
        ->whereNull('ended_at')
        ->exists())->toBeTrue();
});
