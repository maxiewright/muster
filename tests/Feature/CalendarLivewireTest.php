<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\EventType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->eventType = EventType::factory()->create(['slug' => 'huddle', 'name' => 'Huddle']);
    actingAs($this->user);
});

it('can navigate to current month', function () {
    $component = Livewire::test('calendar.calendar-view');

    $component->set('currentMonth', CarbonImmutable::now()->subMonth()->startOfMonth());

    $component->call('goToCurrentMonth')
        ->assertSet('currentMonth', CarbonImmutable::now()->startOfMonth());
});

it('can open event for editing', function () {
    $event = Event::factory()->create([
        'user_id' => $this->user->id,
        'event_type_id' => $this->eventType->id,
        'title' => 'Test Event',
    ]);

    Livewire::test('calendar.calendar-view')
        ->call('editEvent', $event->id)
        ->assertSet('editingEventId', $event->id)
        ->assertSet('showCreateModal', true);
});

it('can move event to another day via drag and drop', function () {
    $event = Event::factory()->create([
        'user_id' => $this->user->id,
        'event_type_id' => $this->eventType->id,
        'starts_at' => now()->startOfMonth()->addDays(2)->setTime(10, 0),
    ]);

    $newDate = now()->startOfMonth()->addDays(5)->format('Y-m-d');

    Livewire::test('calendar.calendar-view')
        ->call('onEventDropped', $event->id, $newDate);

    $event->refresh();
    expect($event->starts_at->format('Y-m-d'))->toBe($newDate);
    expect($event->starts_at->format('H:i'))->toBe('10:00');
});

it('can save an existing event in the modal', function () {
    $event = Event::factory()->create([
        'user_id' => $this->user->id,
        'event_type_id' => $this->eventType->id,
        'title' => 'Original Title',
    ]);

    Livewire::test('calendar.create-event-modal', ['eventId' => $event->id])
        ->set('title', 'Updated Title')
        ->call('save')
        ->assertDispatched('saved');

    expect($event->fresh()->title)->toBe('Updated Title');
});

it('uses the correct timezone for today', function () {
    // Port of Spain is UTC-4
    config(['app.timezone' => 'America/Port_of_Spain']);

    $nowInPOS = CarbonImmutable::now('America/Port_of_Spain');
    $component = Livewire::test('calendar.calendar-view');

    expect($component->get('currentMonth')->timezone->getName())->toBe('America/Port_of_Spain');
});
