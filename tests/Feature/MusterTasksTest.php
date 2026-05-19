<?php

declare(strict_types=1);

use App\Enums\MusterTaskStatus;
use App\Enums\TaskStatus;
use App\Enums\UnitMembershipRole;
use App\Models\Muster;
use App\Models\MusterTask;
use App\Models\Organization;
use App\Models\Task;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

function attachMusterTaskUserToUnit(User $user, Organization $organization, Unit $unit): void
{
    $user->forceFill(['organization_id' => $organization->id])->save();

    UnitMembership::query()->firstOrCreate([
        'user_id' => $user->id,
        'unit_id' => $unit->id,
    ], [
        'role' => UnitMembershipRole::Member,
    ]);
}

test('muster tasks component denies access for another users muster', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $muster = Muster::factory()->create([
        'user_id' => $otherUser->id,
        'date' => today(),
    ]);

    actingAs($user);

    Livewire::test('muster-tasks', ['musterId' => $muster->id])
        ->assertStatus(403);
});

test('muster tasks component exposes the canonical muster api', function (): void {
    $user = User::factory()->create();
    $muster = Muster::factory()->create([
        'user_id' => $user->id,
        'date' => today(),
    ]);

    actingAs($user);

    $component = Livewire::test('muster-tasks', ['musterId' => $muster->id]);

    expect($component->get('muster')->is($muster))->toBeTrue();
});

test('mark complete updates muster task and task status', function (): void {
    $user = User::factory()->create();
    $muster = Muster::factory()->create([
        'user_id' => $user->id,
        'date' => today(),
    ]);
    $task = Task::factory()->create([
        'assigned_to' => $user->id,
        'created_by' => $user->id,
        'status' => TaskStatus::InProgress,
    ]);
    MusterTask::create([
        'muster_id' => $muster->id,
        'task_id' => $task->id,
        'status' => MusterTaskStatus::Ongoing,
    ]);

    actingAs($user);

    Livewire::test('muster-tasks', ['musterId' => $muster->id])
        ->call('markComplete', $task->id);

    $this->assertDatabaseHas('muster_task', [
        'muster_id' => $muster->id,
        'task_id' => $task->id,
        'status' => MusterTaskStatus::Completed->value,
    ]);
    expect($task->fresh()->status)->toBe(TaskStatus::Completed);
});

test('start task updates muster task to ongoing and task to in progress', function (): void {
    $user = User::factory()->create();
    $muster = Muster::factory()->create([
        'user_id' => $user->id,
        'date' => today(),
    ]);
    $task = Task::factory()->create([
        'assigned_to' => $user->id,
        'created_by' => $user->id,
        'status' => TaskStatus::Todo,
    ]);
    MusterTask::create([
        'muster_id' => $muster->id,
        'task_id' => $task->id,
        'status' => MusterTaskStatus::Planned,
    ]);

    actingAs($user);

    Livewire::test('muster-tasks', ['musterId' => $muster->id])
        ->call('startTask', $task->id);

    $this->assertDatabaseHas('muster_task', [
        'muster_id' => $muster->id,
        'task_id' => $task->id,
        'status' => MusterTaskStatus::Ongoing->value,
    ]);
    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

test('toggle blocked sets task and muster task to blocked then unblocks', function (): void {
    $user = User::factory()->create();
    $muster = Muster::factory()->create([
        'user_id' => $user->id,
        'date' => today(),
    ]);
    $task = Task::factory()->create([
        'assigned_to' => $user->id,
        'created_by' => $user->id,
        'status' => TaskStatus::InProgress,
    ]);
    MusterTask::create([
        'muster_id' => $muster->id,
        'task_id' => $task->id,
        'status' => MusterTaskStatus::Planned,
    ]);

    actingAs($user);

    $component = Livewire::test('muster-tasks', ['musterId' => $muster->id]);

    $component->call('toggleBlocked', $task->id);

    $this->assertDatabaseHas('muster_task', [
        'muster_id' => $muster->id,
        'task_id' => $task->id,
        'status' => MusterTaskStatus::Blocked->value,
    ]);
    expect($task->fresh()->status)->toBe(TaskStatus::Blocked);

    $component->call('toggleBlocked', $task->id);

    $this->assertDatabaseHas('muster_task', [
        'muster_id' => $muster->id,
        'task_id' => $task->id,
        'status' => MusterTaskStatus::Planned->value,
    ]);
    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

test('muster tasks component denies access for a muster outside the active unit', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $user = User::factory()->create(['organization_id' => $organization->id]);

    attachMusterTaskUserToUnit($user, $organization, $alphaUnit);
    attachMusterTaskUserToUnit($user, $organization, $bravoUnit);

    $muster = Muster::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $bravoUnit->id,
        'date' => today(),
    ]);

    actingAs($user);
    session(['active_unit_id' => $alphaUnit->id]);

    Livewire::test('muster-tasks', ['musterId' => $muster->id])
        ->assertStatus(403);
});
