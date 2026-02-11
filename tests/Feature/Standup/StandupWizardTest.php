<?php

declare(strict_types=1);

use App\Enums\Mood;
use App\Enums\StandupTaskStatus;
use App\Enums\TaskStatus;
use App\Models\Standup;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('renders the standup wizard component', function () {
    Livewire::test('standup.standup-form')
        ->assertSuccessful()
        ->assertSee('What did you work on yesterday?');
});

it('shows edit mode when user already has todays standup', function () {
    Standup::create([
        'user_id' => $this->user->id,
        'date' => today(),
        'mood' => Mood::Steady->value,
    ]);

    Livewire::test('standup.standup-form')
        ->assertSee('Edit Check-in');
});

it('loads yesterday tasks from previous standup', function () {
    $task = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::InProgress,
    ]);

    $previousStandup = Standup::create([
        'user_id' => $this->user->id,
        'date' => today()->subDay(),
        'mood' => Mood::Steady->value,
    ]);

    DB::table('standup_task')->insert([
        'standup_id' => $previousStandup->id,
        'task_id' => $task->id,
        'status' => StandupTaskStatus::Planned->value,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test('standup.standup-form')
        ->assertSee($task->title);
});

it('allows toggling completed tasks', function () {
    $task = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::InProgress,
    ]);

    Livewire::test('standup.standup-form')
        ->call('toggleCompleted', $task->id)
        ->assertSet('completedTaskIds', [$task->id])
        ->call('toggleCompleted', $task->id)
        ->assertSet('completedTaskIds', []);
});

it('navigates between steps', function () {
    Livewire::test('standup.standup-form')
        ->assertSet('currentStep', 1)
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->call('nextStep')
        ->assertSet('currentStep', 3)
        ->call('previousStep')
        ->assertSet('currentStep', 2)
        ->call('goToStep', 1)
        ->assertSet('currentStep', 1);
});

it('shows backlog tasks in step 2', function () {
    $task = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('standup.standup-form')
        ->call('nextStep')
        ->assertSee($task->title);
});

it('filters backlog tasks by search', function () {
    $task1 = Task::factory()->create([
        'title' => 'Fix authentication bug',
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    $task2 = Task::factory()->create([
        'title' => 'Update dashboard layout',
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('standup.standup-form')
        ->call('nextStep')
        ->set('taskSearch', 'authentication')
        ->assertSee($task1->title)
        ->assertDontSee($task2->title);
});

it('creates quick task', function () {
    Livewire::test('standup.standup-form')
        ->call('nextStep')
        ->set('newTaskTitle', 'New urgent task')
        ->call('createQuickTask')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tasks', [
        'title' => 'New urgent task',
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo->value,
    ]);
});

it('does not create quick task with empty title', function () {
    $initialCount = Task::count();

    Livewire::test('standup.standup-form')
        ->call('nextStep')
        ->set('newTaskTitle', '')
        ->call('createQuickTask')
        ->assertHasErrors('newTaskTitle');

    expect(Task::count())->toBe($initialCount);
});

it('does not create quick task with whitespace-only title', function () {
    $initialCount = Task::count();

    Livewire::test('standup.standup-form')
        ->call('nextStep')
        ->set('newTaskTitle', '   ')
        ->call('createQuickTask')
        ->assertHasErrors('newTaskTitle');

    expect(Task::count())->toBe($initialCount);
});

it('toggles planned tasks', function () {
    $task = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('standup.standup-form')
        ->call('nextStep')
        ->call('togglePlanned', $task->id)
        ->assertSet('plannedTaskIds', [$task->id]);
});

it('shows summary in step 3', function () {
    $completedTask = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::InProgress,
    ]);

    $plannedTask = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('standup.standup-form')
        ->set('completedTaskIds', [$completedTask->id])
        ->set('plannedTaskIds', [$plannedTask->id])
        ->call('goToStep', 3)
        ->assertSee('Completed')
        ->assertSee('Planned');
});

it('displays mood selector in step 3', function () {
    Livewire::test('standup.standup-form')
        ->call('goToStep', 3)
        ->assertSee('How are you feeling today?')
        ->assertSee(Mood::Firing->emoji())
        ->assertSee(Mood::Steady->emoji())
        ->assertSee(Mood::Struggling->emoji());
});

it('allows setting mood', function () {
    Livewire::test('standup.standup-form')
        ->call('goToStep', 3)
        ->set('mood', Mood::Firing->value)
        ->assertSet('mood', Mood::Firing->value);
});

it('validates blockers max length', function () {
    Livewire::test('standup.standup-form')
        ->call('goToStep', 3)
        ->set('blockers', str_repeat('a', 1001))
        ->call('submitStandup')
        ->assertHasErrors('blockers');
});

it('submits standup successfully', function () {
    $completedTask = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::InProgress,
    ]);

    $plannedTask = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('standup.standup-form')
        ->set('completedTaskIds', [$completedTask->id])
        ->set('plannedTaskIds', [$plannedTask->id])
        ->set('blockers', 'Waiting for API documentation')
        ->set('mood', Mood::Steady->value)
        ->call('submitStandup')
        ->assertSee('Check-in Complete!');

    $this->assertDatabaseHas('standups', [
        'user_id' => $this->user->id,
        'date' => today(),
        'blockers' => 'Waiting for API documentation',
        'mood' => Mood::Steady->value,
    ]);

    $standup = Standup::where('user_id', $this->user->id)
        ->whereDate('date', today())
        ->first();

    $this->assertDatabaseHas('standup_task', [
        'standup_id' => $standup->id,
        'task_id' => $completedTask->id,
        'status' => StandupTaskStatus::Completed->value,
    ]);

    $this->assertDatabaseHas('standup_task', [
        'standup_id' => $standup->id,
        'task_id' => $plannedTask->id,
        'status' => StandupTaskStatus::Planned->value,
    ]);

    expect($completedTask->fresh()->status)->toBe(TaskStatus::Completed);
    expect($plannedTask->fresh()->status)->toBe(TaskStatus::InProgress);
});

it('updates task statuses correctly after submission', function () {
    $completedTask = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::InProgress,
    ]);

    $plannedTask = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('standup.standup-form')
        ->set('completedTaskIds', [$completedTask->id])
        ->set('plannedTaskIds', [$plannedTask->id])
        ->set('mood', Mood::Strong->value)
        ->call('submitStandup');

    expect($completedTask->fresh()->status)->toBe(TaskStatus::Completed);
    expect($plannedTask->fresh()->status)->toBe(TaskStatus::InProgress);
});

it('handles blocked tasks correctly', function () {
    $blockedTask = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('standup.standup-form')
        ->set('plannedTaskIds', [$blockedTask->id])
        ->set('blockedTaskIds', [$blockedTask->id])
        ->set('mood', Mood::Blocked->value)
        ->call('submitStandup');

    $standup = Standup::where('user_id', $this->user->id)
        ->whereDate('date', today())
        ->first();

    $this->assertDatabaseHas('standup_task', [
        'standup_id' => $standup->id,
        'task_id' => $blockedTask->id,
        'status' => StandupTaskStatus::Blocked->value,
    ]);

    expect($blockedTask->fresh()->status)->toBe(TaskStatus::Blocked);
});

it('can submit standup without tasks', function () {
    Livewire::test('standup.standup-form')
        ->set('blockers', 'No blockers today')
        ->set('mood', Mood::Firing->value)
        ->call('submitStandup')
        ->assertSee('Check-in Complete!');

    $this->assertDatabaseHas('standups', [
        'user_id' => $this->user->id,
        'date' => today(),
        'blockers' => 'No blockers today',
        'mood' => Mood::Firing->value,
    ]);
});

it('can submit standup without blockers and mood', function () {
    Livewire::test('standup.standup-form')
        ->set('blockers', '')
        ->set('mood', null)
        ->call('submitStandup')
        ->assertSee('Check-in Complete!');

    $this->assertDatabaseHas('standups', [
        'user_id' => $this->user->id,
        'date' => today(),
        'blockers' => null,
        'mood' => null,
    ]);
});

it('moves planned task to ongoing when start is clicked', function () {
    $task = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('standup.standup-form')
        ->call('nextStep')
        ->call('togglePlanned', $task->id)
        ->assertSet('plannedTaskIds', [$task->id])
        ->call('startTaskToOngoing', $task->id)
        ->assertSet('plannedTaskIds', [])
        ->assertSet('ongoingTaskIds', [$task->id]);
});

it('persists ongoing tasks on submit', function () {
    $task = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('standup.standup-form')
        ->set('ongoingTaskIds', [$task->id])
        ->set('mood', Mood::Steady->value)
        ->call('submitStandup')
        ->assertSee('Check-in Complete!');

    $standup = Standup::where('user_id', $this->user->id)->whereDate('date', today())->first();
    $this->assertDatabaseHas('standup_task', [
        'standup_id' => $standup->id,
        'task_id' => $task->id,
        'status' => StandupTaskStatus::Ongoing->value,
    ]);
    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});
