<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\EventType;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Carbon\CarbonImmutable;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->eventType = EventType::factory()->create(['slug' => 'huddle', 'name' => 'Huddle']);
    actingAs($this->user);
});

function attachCalendarLivewireUserToUnit(User $user, Organization $organization, Unit $unit, string $role = 'member'): void
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

it('can navigate to current month', function (): void {
    $component = Livewire::test('calendar.calendar-view');

    $component->set('currentMonth', CarbonImmutable::now()->subMonth()->startOfMonth());

    $component->call('goToCurrentMonth')
        ->assertSet('currentMonth', CarbonImmutable::now()->startOfMonth());
});

it('can open event for editing', function (): void {
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

it('can move event to another day via drag and drop', function (): void {
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

it('forbids moving another users event via drag and drop', function (): void {
    $otherUser = User::factory()->create();
    $event = Event::factory()->create([
        'user_id' => $otherUser->id,
        'event_type_id' => $this->eventType->id,
        'starts_at' => now()->startOfMonth()->addDays(2)->setTime(10, 0),
    ]);

    Livewire::test('calendar.calendar-view')
        ->call('onEventDropped', $event->id, now()->startOfMonth()->addDays(5)->format('Y-m-d'))
        ->assertForbidden();
});

it('can save an existing event in the modal', function (): void {
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

it('forbids opening another users event in the modal', function (): void {
    $otherUser = User::factory()->create();
    $event = Event::factory()->create([
        'user_id' => $otherUser->id,
        'event_type_id' => $this->eventType->id,
    ]);

    Livewire::test('calendar.create-event-modal', ['eventId' => $event->id])
        ->assertForbidden();
});

it('forbids updating another users event in the modal', function (): void {
    $otherUser = User::factory()->create();
    $event = Event::factory()->create([
        'user_id' => $otherUser->id,
        'event_type_id' => $this->eventType->id,
        'title' => 'Original Title',
    ]);

    Livewire::test('calendar.create-event-modal')
        ->set('eventId', $event->id)
        ->set('title', 'Compromised Title')
        ->set('starts_at', now()->format('Y-m-d\TH:i'))
        ->set('ends_at', now()->addHour()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertForbidden();
});

it('uses the correct timezone for today', function (): void {
    // Port of Spain is UTC-4
    config(['app.timezone' => 'America/Port_of_Spain']);

    $nowInPOS = CarbonImmutable::now('America/Port_of_Spain');
    $component = Livewire::test('calendar.calendar-view');

    expect($component->get('currentMonth')->timezone->getName())->toBe('America/Port_of_Spain');
});

it('creates events inside the active unit context', function (): void {
    $organization = Organization::query()->create(['name' => 'Ops', 'slug' => 'ops']);
    $unit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Alpha', 'slug' => 'alpha']);
    attachCalendarLivewireUserToUnit($this->user, $organization, $unit, 'commander');

    $this->withSession(['active_unit_id' => $unit->id]);

    Livewire::test('calendar.create-event-modal')
        ->set('title', 'Unit Briefing')
        ->set('type', 'huddle')
        ->set('starts_at', now()->format('Y-m-d\TH:i'))
        ->set('ends_at', now()->addHour()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertDispatched('saved');

    $event = Event::query()->where('title', 'Unit Briefing')->first();

    expect($event)->not->toBeNull();
    expect($event?->organization_id)->toBe($organization->id);
    expect($event?->unit_id)->toBe($unit->id);
});
