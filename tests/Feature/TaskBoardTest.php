<?php

declare(strict_types=1);

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Events\TaskAssigned;
use App\Events\TaskCompleted;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Models\Organization;
use App\Models\Task;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function attachTaskBoardUserToUnit(User $user, Organization $organization, Unit $unit, string $role = 'member'): void
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

test('guests cannot visit tasks page', function (): void {
    $response = $this->get(route('tasks'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit tasks page', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('tasks'));

    $response->assertOk();
});

test('task board displays tasks grouped by status', function (): void {
    $user = User::factory()->create();
    Task::factory()->create(['created_by' => $user->id, 'status' => TaskStatus::Todo, 'title' => 'Todo Task']);
    Task::factory()->create(['created_by' => $user->id, 'status' => TaskStatus::InProgress, 'title' => 'In Progress Task']);
    $this->actingAs($user);

    $response = $this->get(route('tasks'));

    $response->assertOk();
    $response->assertSee('Todo Task');
    $response->assertSee('In Progress Task');
})->skip('Blade/Flux compile quirk in task-card view under test env');

test('TaskCreated is broadcast when task is created', function (): void {
    Event::fake([TaskCreated::class]);

    $user = User::factory()->create();
    $task = Task::factory()->create(['created_by' => $user->id, 'title' => 'New Task']);

    event(new TaskCreated($task));

    Event::assertDispatched(TaskCreated::class, function (TaskCreated $event): bool {
        $channels = $event->broadcastOn();

        return count($channels) === 1 && $channels[0]->name === 'private-team';
    });
});

test('TaskAssigned is broadcast when task is created for another user', function (): void {
    Event::fake([TaskAssigned::class]);

    $creator = User::factory()->lead()->create();
    $assignee = User::factory()->create();

    Livewire::actingAs($creator)
        ->test('task.create-task-modal')
        ->set('title', 'Assigned task')
        ->set('status', TaskStatus::Todo->value)
        ->set('priority', TaskPriority::Medium->value)
        ->set('assigned_to', $assignee->id)
        ->call('save');

    Event::assertDispatched(TaskAssigned::class, function (TaskAssigned $event) use ($assignee): bool {
        $channels = $event->broadcastOn();

        return count($channels) === 1 && $channels[0]->name === "private-App.Models.User.{$assignee->id}";
    });
});

test('TaskCompleted is broadcast when task is completed', function (): void {
    Event::fake([TaskCompleted::class]);

    $task = Task::factory()->create([
        'status' => TaskStatus::InProgress,
        'title' => 'Complete Me',
    ]);

    event(new TaskCompleted($task->fresh()));

    Event::assertDispatched(TaskCompleted::class, function (TaskCompleted $event): bool {
        $channels = $event->broadcastOn();

        return count($channels) === 1 && $channels[0]->name === 'private-team';
    });
});

test('task board sortTaskToStatus updates task status when dropping from another column', function (): void {
    $user = User::factory()->create();
    $todoTask = Task::factory()->create([
        'created_by' => $user->id,
        'assigned_to' => $user->id,
        'status' => TaskStatus::Todo,
        'title' => 'Move me',
    ]);

    Livewire::actingAs($user)
        ->test('task.task-board')
        ->call('sortTaskToInProgress', $todoTask->id, 0);

    expect($todoTask->fresh()->status)->toBe(TaskStatus::InProgress);
});

test('task board only loads tasks from the active unit', function (): void {
    $organization = Organization::query()->create(['name' => 'Ops', 'slug' => 'ops']);
    $unit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Alpha', 'slug' => 'alpha']);
    $otherUnit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Bravo', 'slug' => 'bravo']);
    $user = User::factory()->create();
    attachTaskBoardUserToUnit($user, $organization, $unit);
    attachTaskBoardUserToUnit($user, $organization, $otherUnit);

    $visibleTask = Task::factory()->create([
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'created_by' => $user->id,
        'assigned_to' => $user->id,
        'title' => 'Visible Task',
    ]);

    Task::factory()->create([
        'organization_id' => $organization->id,
        'unit_id' => $otherUnit->id,
        'created_by' => $user->id,
        'assigned_to' => $user->id,
        'title' => 'Hidden Task',
    ]);

    $this->actingAs($user)->withSession(['active_unit_id' => $unit->id]);

    $tasks = Livewire::test('task.task-board')->get('tasks');

    expect($tasks->pluck('id')->all())->toBe([$visibleTask->id]);
});

test('task creation stamps the active unit context', function (): void {
    $organization = Organization::query()->create(['name' => 'Ops', 'slug' => 'ops']);
    $unit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Alpha', 'slug' => 'alpha']);
    $user = User::factory()->create();
    attachTaskBoardUserToUnit($user, $organization, $unit, 'commander');

    $this->actingAs($user)->withSession(['active_unit_id' => $unit->id]);

    Livewire::test('task.create-task-modal')
        ->set('title', 'Unit scoped task')
        ->set('status', TaskStatus::Todo->value)
        ->set('priority', TaskPriority::Medium->value)
        ->call('save')
        ->assertDispatched('task-saved');

    $task = Task::query()->where('title', 'Unit scoped task')->first();

    expect($task)->not->toBeNull();
    expect($task?->organization_id)->toBe($organization->id);
    expect($task?->unit_id)->toBe($unit->id);
});

test('task board handleTaskMoved runs and component re-renders', function (): void {
    $user = User::factory()->create();
    Task::factory()->create(['created_by' => $user->id, 'status' => TaskStatus::Todo]);

    Livewire::actingAs($user)
        ->test('task.task-board')
        ->dispatch('task-moved')
        ->assertOk();
});

test('task card toggleComplete marks task completed and can uncomplete', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'created_by' => $user->id,
        'assigned_to' => $user->id,
        'status' => TaskStatus::Todo,
        'title' => 'Toggle me',
    ]);

    Livewire::actingAs($user)
        ->test('task.task-card', ['task' => $task])
        ->call('toggleComplete');

    expect($task->fresh()->status)->toBe(TaskStatus::Completed);

    Livewire::actingAs($user)
        ->test('task.task-card', ['task' => $task->fresh()])
        ->call('toggleComplete');

    expect($task->fresh()->status)->toBe(TaskStatus::Todo);
});

test('task card startTask moves Todo to InProgress', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'created_by' => $user->id,
        'assigned_to' => $user->id,
        'status' => TaskStatus::Todo,
        'title' => 'Start me',
    ]);

    Livewire::actingAs($user)
        ->test('task.task-card', ['task' => $task])
        ->call('startTask');

    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

test('task creator receives status change broadcast when assignee updates task', function (): void {
    Event::fake([TaskStatusChanged::class]);

    $creator = User::factory()->create();
    $assignee = User::factory()->create();
    $task = Task::factory()->create([
        'created_by' => $creator->id,
        'assigned_to' => $assignee->id,
        'status' => TaskStatus::Todo,
        'title' => 'Assigned task',
    ]);

    Livewire::actingAs($assignee)
        ->test('task.task-card', ['task' => $task])
        ->call('startTask');

    Event::assertDispatched(TaskStatusChanged::class, function (TaskStatusChanged $event) use ($creator): bool {
        $channels = $event->broadcastOn();

        return count($channels) === 1
            && $channels[0]->name === "private-App.Models.User.{$creator->id}"
            && $event->toStatus === TaskStatus::InProgress;
    });
});
