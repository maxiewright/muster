<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user with create_tasks permission can create tasks', function (): void {
    $user = User::factory()->create(['role' => Role::Lead]);

    expect($user->can('create', Task::class))->toBeTrue();
});

test('member can create tasks', function (): void {
    $user = User::factory()->create(['role' => Role::Member]);

    expect($user->can('create', Task::class))->toBeTrue();
});

test('user can update task they created', function (): void {
    $user = User::factory()->create(['role' => Role::Member]);
    $task = Task::factory()->create(['created_by' => $user->id]);

    expect($user->can('update', $task))->toBeTrue();
});

test('user can update task they are assigned to', function (): void {
    $user = User::factory()->create(['role' => Role::Member]);
    $task = Task::factory()->create(['assigned_to' => $user->id]);

    expect($user->can('update', $task))->toBeTrue();
});

test('lead can update any task', function (): void {
    $lead = User::factory()->create(['role' => Role::Lead]);
    $other = User::factory()->create(['role' => Role::Member]);
    $task = Task::factory()->create(['created_by' => $other->id, 'assigned_to' => $other->id]);

    expect($lead->can('update', $task))->toBeTrue();
});

test('member cannot update task they did not create or are not assigned to', function (): void {
    $user = User::factory()->create(['role' => Role::Member]);
    $other = User::factory()->create(['role' => Role::Member]);
    $task = Task::factory()->create(['created_by' => $other->id, 'assigned_to' => $other->id]);

    expect($user->can('update', $task))->toBeFalse();
});

test('lead can delete any task', function (): void {
    $lead = User::factory()->create(['role' => Role::Lead]);
    $task = Task::factory()->create();

    expect($lead->can('delete', $task))->toBeTrue();
});

test('creator can delete their own task', function (): void {
    $user = User::factory()->create(['role' => Role::Member]);
    $task = Task::factory()->create(['created_by' => $user->id]);

    expect($user->can('delete', $task))->toBeTrue();
});

test('member cannot delete task they did not create', function (): void {
    $user = User::factory()->create(['role' => Role::Member]);
    $other = User::factory()->create(['role' => Role::Member]);
    $task = Task::factory()->create(['created_by' => $other->id]);

    expect($user->can('delete', $task))->toBeFalse();
});
