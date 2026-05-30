<?php

declare(strict_types=1);

use App\Enums\Mood;
use App\Enums\MusterTaskStatus;
use App\Enums\TaskStatus;
use App\Models\Muster;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('renders the muster wizard component', function (): void {
    Livewire::test('muster.muster-form')
        ->assertSuccessful()
        ->assertSee('What did you work on yesterday?');
});

it('shows edit mode when user already has todays muster', function (): void {
    Muster::create([
        'user_id' => $this->user->id,
        'date' => today(),
        'mood' => Mood::Steady->value,
    ]);

    Livewire::test('muster.muster-form')
        ->assertSee('Edit Muster');
});

it('exposes the canonical muster property when editing an existing muster', function (): void {
    $muster = Muster::create([
        'user_id' => $this->user->id,
        'date' => today(),
        'mood' => Mood::Steady->value,
    ]);

    $component = Livewire::test('muster.muster-form', ['muster' => $muster]);

    expect($component->get('muster')?->is($muster))->toBeTrue();
});

it('loads yesterday tasks from previous muster', function (): void {
    $task = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::InProgress,
    ]);

    $previousMuster = Muster::create([
        'user_id' => $this->user->id,
        'date' => today()->subDay(),
        'mood' => Mood::Steady->value,
    ]);

    DB::table('muster_task')->insert([
        'muster_id' => $previousMuster->id,
        'task_id' => $task->id,
        'status' => MusterTaskStatus::Planned->value,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test('muster.muster-form')
        ->assertSee($task->title);
});

it('allows toggling completed tasks', function (): void {
    $task = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::InProgress,
    ]);

    Livewire::test('muster.muster-form')
        ->call('toggleCompleted', $task->id)
        ->assertSet('completedTaskIds', [$task->id])
        ->call('toggleCompleted', $task->id)
        ->assertSet('completedTaskIds', []);
});

it('navigates between steps', function (): void {
    Livewire::test('muster.muster-form')
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

it('shows backlog tasks in step 2', function (): void {
    $task = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('muster.muster-form')
        ->call('nextStep')
        ->assertSee($task->title);
});

it('filters backlog tasks by search', function (): void {
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

    Livewire::test('muster.muster-form')
        ->call('nextStep')
        ->set('taskSearch', 'authentication')
        ->assertSee($task1->title)
        ->assertDontSee($task2->title);
});

it('creates quick task', function (): void {
    Livewire::test('muster.muster-form')
        ->call('nextStep')
        ->set('form.newTaskTitle', 'New urgent task')
        ->call('createQuickTask')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tasks', [
        'title' => 'New urgent task',
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo->value,
    ]);
});

it('does not create quick task with empty title', function (): void {
    $initialCount = Task::count();

    Livewire::test('muster.muster-form')
        ->call('nextStep')
        ->set('form.newTaskTitle', '')
        ->call('createQuickTask')
        ->assertHasErrors('form.newTaskTitle');

    expect(Task::count())->toBe($initialCount);
});

it('does not create quick task with whitespace-only title', function (): void {
    $initialCount = Task::count();

    Livewire::test('muster.muster-form')
        ->call('nextStep')
        ->set('form.newTaskTitle', '   ')
        ->call('createQuickTask')
        ->assertHasErrors('form.newTaskTitle');

    expect(Task::count())->toBe($initialCount);
});

it('toggles planned tasks', function (): void {
    $task = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('muster.muster-form')
        ->call('nextStep')
        ->call('togglePlanned', $task->id)
        ->assertSet('plannedTaskIds', [$task->id]);
});

it('shows summary in step 3', function (): void {
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

    Livewire::test('muster.muster-form')
        ->set('completedTaskIds', [$completedTask->id])
        ->set('plannedTaskIds', [$plannedTask->id])
        ->call('goToStep', 3)
        ->assertSee('Completed')
        ->assertSee('Planned');
});

it('displays mood selector in step 3', function (): void {
    Livewire::test('muster.muster-form')
        ->call('goToStep', 3)
        ->assertSee('How are you feeling today?')
        ->assertSee(Mood::Firing->emoji())
        ->assertSee(Mood::Steady->emoji())
        ->assertSee(Mood::Struggling->emoji());
});

it('allows setting mood', function (): void {
    Livewire::test('muster.muster-form')
        ->call('goToStep', 3)
        ->set('form.mood', Mood::Firing->value)
        ->assertSet('form.mood', Mood::Firing->value);
});

it('validates blockers max length', function (): void {
    Livewire::test('muster.muster-form')
        ->call('goToStep', 3)
        ->set('form.blockers', str_repeat('a', 1001))
        ->call('submitMuster')
        ->assertHasErrors('form.blockers');
});

it('submits muster successfully', function (): void {
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

    Livewire::test('muster.muster-form')
        ->set('completedTaskIds', [$completedTask->id])
        ->set('plannedTaskIds', [$plannedTask->id])
        ->set('form.blockers', 'Waiting for API documentation')
        ->set('form.mood', Mood::Steady->value)
        ->call('submitMuster')
        ->assertSee('Muster Complete!');

    $this->assertDatabaseHas('musters', [
        'user_id' => $this->user->id,
        'date' => today(),
        'blockers' => 'Waiting for API documentation',
        'mood' => Mood::Steady->value,
    ]);

    $muster = Muster::where('user_id', $this->user->id)
        ->whereDate('date', today())
        ->first();

    $this->assertDatabaseHas('muster_task', [
        'muster_id' => $muster->id,
        'task_id' => $completedTask->id,
        'status' => MusterTaskStatus::Completed->value,
    ]);

    $this->assertDatabaseHas('muster_task', [
        'muster_id' => $muster->id,
        'task_id' => $plannedTask->id,
        'status' => MusterTaskStatus::Planned->value,
    ]);

    expect($completedTask->fresh()->status)->toBe(TaskStatus::Completed);
    expect($plannedTask->fresh()->status)->toBe(TaskStatus::InProgress);
});

it('updates task statuses correctly after submission', function (): void {
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

    Livewire::test('muster.muster-form')
        ->set('completedTaskIds', [$completedTask->id])
        ->set('plannedTaskIds', [$plannedTask->id])
        ->set('form.mood', Mood::Strong->value)
        ->call('submitMuster');

    expect($completedTask->fresh()->status)->toBe(TaskStatus::Completed);
    expect($plannedTask->fresh()->status)->toBe(TaskStatus::InProgress);
});

it('handles blocked tasks correctly', function (): void {
    $blockedTask = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('muster.muster-form')
        ->set('plannedTaskIds', [$blockedTask->id])
        ->set('blockedTaskIds', [$blockedTask->id])
        ->set('form.mood', Mood::Blocked->value)
        ->call('submitMuster');

    $muster = Muster::where('user_id', $this->user->id)
        ->whereDate('date', today())
        ->first();

    $this->assertDatabaseHas('muster_task', [
        'muster_id' => $muster->id,
        'task_id' => $blockedTask->id,
        'status' => MusterTaskStatus::Blocked->value,
    ]);

    expect($blockedTask->fresh()->status)->toBe(TaskStatus::Blocked);
});

it('can submit muster without tasks', function (): void {
    Livewire::test('muster.muster-form')
        ->set('form.blockers', 'No blockers today')
        ->set('form.mood', Mood::Firing->value)
        ->call('submitMuster')
        ->assertSee('Muster Complete!');

    $this->assertDatabaseHas('musters', [
        'user_id' => $this->user->id,
        'date' => today(),
        'blockers' => 'No blockers today',
        'mood' => Mood::Firing->value,
    ]);
});

it('can submit muster without blockers and mood', function (): void {
    Livewire::test('muster.muster-form')
        ->set('form.blockers', '')
        ->set('form.mood', null)
        ->call('submitMuster')
        ->assertSee('Muster Complete!');

    $this->assertDatabaseHas('musters', [
        'user_id' => $this->user->id,
        'date' => today(),
        'blockers' => null,
        'mood' => null,
    ]);
});

it('moves planned task to ongoing when start is clicked', function (): void {
    $task = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('muster.muster-form')
        ->call('nextStep')
        ->call('togglePlanned', $task->id)
        ->assertSet('plannedTaskIds', [$task->id])
        ->call('startTaskToOngoing', $task->id)
        ->assertSet('plannedTaskIds', [])
        ->assertSet('ongoingTaskIds', [$task->id]);
});

it('persists ongoing tasks on submit', function (): void {
    $task = Task::factory()->create([
        'assigned_to' => $this->user->id,
        'created_by' => $this->user->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('muster.muster-form')
        ->set('ongoingTaskIds', [$task->id])
        ->set('form.mood', Mood::Steady->value)
        ->call('submitMuster')
        ->assertSee('Muster Complete!');

    $muster = Muster::where('user_id', $this->user->id)->whereDate('date', today())->first();
    $this->assertDatabaseHas('muster_task', [
        'muster_id' => $muster->id,
        'task_id' => $task->id,
        'status' => MusterTaskStatus::Ongoing->value,
    ]);

    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

it('forbids submitting muster with another users task ids', function (): void {
    $otherUser = User::factory()->create();
    $foreignTask = Task::factory()->create([
        'assigned_to' => $otherUser->id,
        'created_by' => $otherUser->id,
        'status' => TaskStatus::Todo,
    ]);

    Livewire::test('muster.muster-form')
        ->set('plannedTaskIds', [$foreignTask->id])
        ->set('form.mood', Mood::Steady->value)
        ->call('submitMuster')
        ->assertForbidden();

    expect($foreignTask->fresh()->status)->toBe(TaskStatus::Todo);
    expect(
        Muster::query()->where('user_id', $this->user->id)->whereDate('date', today())->exists()
    )->toBeFalse();
});
