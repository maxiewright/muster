<?php

declare(strict_types=1);

use App\Enums\TaskStatus;
use App\Events\TaskAssigned;
use App\Events\TaskCompleted;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

test('TaskCreated implements ShouldBroadcast', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->create(['created_by' => $user->id]);

    $event = new TaskCreated($task);

    expect($event)->toBeInstanceOf(ShouldBroadcast::class);
});

test('TaskCreated broadcasts on team private channel', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->create(['created_by' => $user->id]);

    $event = new TaskCreated($task);
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1);
    expect($channels[0])->toBeInstanceOf(PrivateChannel::class);
    expect($channels[0]->name)->toBe('private-team');
});

test('TaskCreated broadcast payload contains task data', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'created_by' => $user->id,
        'title' => 'Test Task',
        'status' => \App\Enums\TaskStatus::Todo,
    ]);

    $event = new TaskCreated($task);
    $payload = $event->broadcastWith();

    expect($payload)->toHaveKey('task');
    expect($payload['task']['title'])->toBe('Test Task');
    expect($payload['task']['status'])->toBe('todo');
    expect($payload['task']['creator_name'])->toBe($user->name);
});

test('TaskCompleted implements ShouldBroadcast', function (): void {
    $task = Task::factory()->completed()->create();

    $event = new TaskCompleted($task);

    expect($event)->toBeInstanceOf(ShouldBroadcast::class);
});

test('TaskCompleted broadcasts on team private channel', function (): void {
    $task = Task::factory()->completed()->create();

    $event = new TaskCompleted($task);
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1);
    expect($channels[0])->toBeInstanceOf(PrivateChannel::class);
    expect($channels[0]->name)->toBe('private-team');
});

test('TaskCompleted broadcast payload contains task data', function (): void {
    $assignee = User::factory()->create(['name' => 'Assignee User']);
    $task = Task::factory()->completed()->create([
        'assigned_to' => $assignee->id,
        'title' => 'Done Task',
    ]);

    $event = new TaskCompleted($task);
    $payload = $event->broadcastWith();

    expect($payload)->toHaveKey('task');
    expect($payload['task']['title'])->toBe('Done Task');
    expect($payload['task']['status'])->toBe('completed');
    expect($payload['task']['assignee_name'])->toBe('Assignee User');
});

test('TaskAssigned implements ShouldBroadcast', function (): void {
    $assignee = User::factory()->create();
    $creator = User::factory()->create();
    $task = Task::factory()->create([
        'created_by' => $creator->id,
        'assigned_to' => $assignee->id,
    ]);

    $event = new TaskAssigned($task);

    expect($event)->toBeInstanceOf(ShouldBroadcast::class);
});

test('TaskAssigned broadcasts on assignee private channel', function (): void {
    $assignee = User::factory()->create();
    $creator = User::factory()->create();
    $task = Task::factory()->create([
        'created_by' => $creator->id,
        'assigned_to' => $assignee->id,
    ]);

    $event = new TaskAssigned($task);
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1);
    expect($channels[0])->toBeInstanceOf(PrivateChannel::class);
    expect($channels[0]->name)->toBe("private-App.Models.User.{$assignee->id}");
});

test('TaskStatusChanged implements ShouldBroadcast', function (): void {
    $assignee = User::factory()->create();
    $creator = User::factory()->create();
    $actor = User::factory()->create();
    $task = Task::factory()->create([
        'created_by' => $creator->id,
        'assigned_to' => $assignee->id,
        'status' => TaskStatus::Todo,
    ]);

    $event = new TaskStatusChanged($task, TaskStatus::Todo, TaskStatus::InProgress, $actor);

    expect($event)->toBeInstanceOf(ShouldBroadcast::class);
});

test('TaskStatusChanged broadcasts on creator private channel', function (): void {
    $assignee = User::factory()->create();
    $creator = User::factory()->create();
    $actor = User::factory()->create();
    $task = Task::factory()->create([
        'created_by' => $creator->id,
        'assigned_to' => $assignee->id,
        'status' => TaskStatus::Todo,
    ]);

    $event = new TaskStatusChanged($task, TaskStatus::Todo, TaskStatus::InProgress, $actor);
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1);
    expect($channels[0])->toBeInstanceOf(PrivateChannel::class);
    expect($channels[0]->name)->toBe("private-App.Models.User.{$creator->id}");
});
