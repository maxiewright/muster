<?php

declare(strict_types=1);

use App\Models\FocusArea;
use App\Models\Muster;
use App\Models\MusterTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('task links to musters through the canonical pivot table', function (): void {
    $muster = Muster::factory()->for(User::factory())->create();
    $task = Task::factory()->create();

    $task->musters()->attach($muster->id, [
        'status' => 'planned',
        'notes' => json_encode(['source' => 'test'], JSON_THROW_ON_ERROR),
    ]);

    expect($task->musters()->pluck('musters.id')->all())->toBe([$muster->id]);
});

test('focus area links to musters through the canonical pivot table', function (): void {
    $muster = Muster::factory()->for(User::factory())->create();
    $focusArea = FocusArea::factory()->create();

    $focusArea->musters()->attach($muster->id);

    expect($focusArea->musters()->pluck('musters.id')->all())->toBe([$muster->id]);
});

test('muster task belongs to a muster', function (): void {
    $muster = Muster::factory()->for(User::factory())->create();
    $task = Task::factory()->create();
    $musterTask = MusterTask::query()->create([
        'muster_id' => $muster->id,
        'task_id' => $task->id,
        'status' => 'planned',
        'notes' => ['source' => 'test'],
    ]);

    expect($musterTask->muster->is($muster))->toBeTrue();
});
