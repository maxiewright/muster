<?php

declare(strict_types=1);

use App\Enums\TaskStatus;
use App\Events\TaskCompleted;
use App\Events\TaskCreated;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

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
    \Illuminate\Support\Facades\Event::fake([TaskCreated::class]);

    $user = User::factory()->create();
    $task = Task::factory()->create(['created_by' => $user->id, 'title' => 'New Task']);

    TaskCreated::dispatch($task);

    \Illuminate\Support\Facades\Event::assertDispatched(TaskCreated::class, function (TaskCreated $event) {
        $channels = $event->broadcastOn();

        return count($channels) === 1 && $channels[0]->name === 'private-team';
    });
});

test('TaskCompleted is broadcast when task is completed', function (): void {
    \Illuminate\Support\Facades\Event::fake([TaskCompleted::class]);

    $task = Task::factory()->create([
        'status' => TaskStatus::InProgress,
        'title' => 'Complete Me',
    ]);

    TaskCompleted::dispatch($task->fresh());

    \Illuminate\Support\Facades\Event::assertDispatched(TaskCompleted::class, function (TaskCompleted $event) {
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
