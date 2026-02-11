<?php

declare(strict_types=1);

use App\Enums\StandupTaskStatus;
use App\Enums\TaskStatus;
use App\Models\Standup;
use App\Models\StandUpTask;
use App\Models\Task;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

test('muster standup tasks component denies access for another users standup', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $standup = Standup::factory()->create([
        'user_id' => $otherUser->id,
        'date' => today(),
    ]);

    actingAs($user);

    Livewire::test('muster-standup-tasks', ['standupId' => $standup->id])
        ->assertStatus(403);
});

test('mark complete updates standup task and task status', function (): void {
    $user = User::factory()->create();
    $standup = Standup::factory()->create([
        'user_id' => $user->id,
        'date' => today(),
    ]);
    $task = Task::factory()->create([
        'assigned_to' => $user->id,
        'created_by' => $user->id,
        'status' => TaskStatus::InProgress,
    ]);
    StandUpTask::create([
        'standup_id' => $standup->id,
        'task_id' => $task->id,
        'status' => StandupTaskStatus::Ongoing,
    ]);

    actingAs($user);

    Livewire::test('muster-standup-tasks', ['standupId' => $standup->id])
        ->call('markComplete', $task->id);

    $this->assertDatabaseHas('standup_task', [
        'standup_id' => $standup->id,
        'task_id' => $task->id,
        'status' => StandupTaskStatus::Completed->value,
    ]);
    expect($task->fresh()->status)->toBe(TaskStatus::Completed);
});

test('start task updates standup task to ongoing and task to in progress', function (): void {
    $user = User::factory()->create();
    $standup = Standup::factory()->create([
        'user_id' => $user->id,
        'date' => today(),
    ]);
    $task = Task::factory()->create([
        'assigned_to' => $user->id,
        'created_by' => $user->id,
        'status' => TaskStatus::Todo,
    ]);
    StandUpTask::create([
        'standup_id' => $standup->id,
        'task_id' => $task->id,
        'status' => StandupTaskStatus::Planned,
    ]);

    actingAs($user);

    Livewire::test('muster-standup-tasks', ['standupId' => $standup->id])
        ->call('startTask', $task->id);

    $this->assertDatabaseHas('standup_task', [
        'standup_id' => $standup->id,
        'task_id' => $task->id,
        'status' => StandupTaskStatus::Ongoing->value,
    ]);
    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

test('toggle blocked sets task and standup task to blocked then unblocks', function (): void {
    $user = User::factory()->create();
    $standup = Standup::factory()->create([
        'user_id' => $user->id,
        'date' => today(),
    ]);
    $task = Task::factory()->create([
        'assigned_to' => $user->id,
        'created_by' => $user->id,
        'status' => TaskStatus::InProgress,
    ]);
    StandUpTask::create([
        'standup_id' => $standup->id,
        'task_id' => $task->id,
        'status' => StandupTaskStatus::Planned,
    ]);

    actingAs($user);

    $component = Livewire::test('muster-standup-tasks', ['standupId' => $standup->id]);

    $component->call('toggleBlocked', $task->id);

    $this->assertDatabaseHas('standup_task', [
        'standup_id' => $standup->id,
        'task_id' => $task->id,
        'status' => StandupTaskStatus::Blocked->value,
    ]);
    expect($task->fresh()->status)->toBe(TaskStatus::Blocked);

    $component->call('toggleBlocked', $task->id);

    $this->assertDatabaseHas('standup_task', [
        'standup_id' => $standup->id,
        'task_id' => $task->id,
        'status' => StandupTaskStatus::Planned->value,
    ]);
    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});
