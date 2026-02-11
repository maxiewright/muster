<?php

use App\Models\Event;
use App\Models\EventType;
use App\Models\User;

test('event can be created with all required fields', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();

    $event = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'title' => 'Team Meeting',
        'description' => 'Weekly standup',
        'starts_at' => now(),
        'ends_at' => now()->addHour(),
    ]);

    expect($event)->toBeInstanceOf(Event::class);
    expect($event->title)->toBe('Team Meeting');
    expect($event->description)->toBe('Weekly standup');
    expect($event->user_id)->toBe($user->id);
    expect($event->event_type_id)->toBe($eventType->id);
});

test('event can be created without optional description', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();

    $event = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'title' => 'Quick Meeting',
        'description' => null,
        'starts_at' => now(),
        'ends_at' => now()->addHour(),
    ]);

    expect($event->title)->toBe('Quick Meeting');
    expect($event->description)->toBeNull();
});

test('event can be updated', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();

    $event = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'title' => 'Original Title',
    ]);

    $event->update(['title' => 'Updated Title']);

    expect($event->fresh()->title)->toBe('Updated Title');
});

test('event can be soft deleted', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();

    $event = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
    ]);

    $eventId = $event->id;

    $event->delete();

    expect(Event::find($eventId))->toBeNull();
    expect(Event::withTrashed()->find($eventId))->not->toBeNull();
});

test('event belongs to a user', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();

    $event = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
    ]);

    expect($event->user)->toBeInstanceOf(User::class);
    expect($event->user->id)->toBe($user->id);
});

test('event belongs to an event type', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create(['name' => 'Workshop']);

    $event = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
    ]);

    expect($event->type)->toBeInstanceOf(EventType::class);
    expect($event->type->name)->toBe('Workshop');
});

test('event casts dates correctly', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();

    $startsAt = now();
    $endsAt = now()->addHours(2);

    $event = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
    ]);

    expect($event->starts_at)->toBeInstanceOf(\Carbon\CarbonInterface::class);
    expect($event->ends_at)->toBeInstanceOf(\Carbon\CarbonInterface::class);
});

test('event can be marked as recurring', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();

    $event = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'is_recurring' => true,
    ]);

    expect($event->is_recurring)->toBeTrue();
});

test('event type color accessor works', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create(['color' => '#FF5733']);

    $event = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
    ]);

    expect($event->typeColor)->toBe('#FF5733');
});

test('event can be queried by date range', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();

    // Create events at different times
    $pastEvent = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'starts_at' => now()->subDays(5),
    ]);

    $currentEvent = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'starts_at' => now()->addDays(2),
    ]);

    $futureEvent = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'starts_at' => now()->addDays(10),
    ]);

    $upcomingWeek = Event::where('starts_at', '>=', now())
        ->where('starts_at', '<=', now()->addDays(7))
        ->get();

    expect($upcomingWeek)->toHaveCount(1);
    expect($upcomingWeek->first()->id)->toBe($currentEvent->id);
});
