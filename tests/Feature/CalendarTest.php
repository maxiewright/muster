<?php

use App\Models\Event;
use App\Models\EventType;
use App\Models\User;

test('guests are redirected to login', function (): void {
    $response = $this->get(route('calendar'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the calendar', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('calendar'));

    $response->assertSuccessful();
});

test('calendar displays current month events', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();

    // Create event in current month
    $currentMonthEvent = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'title' => 'Current Month Event',
        'starts_at' => now()->startOfMonth()->addDays(5),
    ]);

    // Create event in next month (should not appear)
    Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'title' => 'Next Month Event',
        'starts_at' => now()->addMonth()->startOfMonth()->addDays(5),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('calendar'));

    $response->assertSuccessful();
    $response->assertSee('Current Month Event');
    $response->assertDontSee('Next Month Event');
});

test('calendar groups events by date', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();

    $targetDate = now()->startOfMonth()->addDays(10);

    // Create multiple events on the same day
    Event::factory()->count(3)->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'starts_at' => $targetDate,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('calendar'));

    $response->assertSuccessful();
});

test('calendar shows events from all users', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $eventType = EventType::factory()->create();

    // Create event for user1
    Event::factory()->create([
        'user_id' => $user1->id,
        'event_type_id' => $eventType->id,
        'title' => 'User 1 Event',
        'starts_at' => now()->startOfMonth()->addDays(5),
    ]);

    // Create event for user2
    Event::factory()->create([
        'user_id' => $user2->id,
        'event_type_id' => $eventType->id,
        'title' => 'User 2 Event',
        'starts_at' => now()->startOfMonth()->addDays(6),
    ]);

    $this->actingAs($user1);

    $response = $this->get(route('calendar'));

    $response->assertSuccessful();
    // Calendar should show events from all users
    $response->assertSee('User 1 Event');
    $response->assertSee('User 2 Event');
});
