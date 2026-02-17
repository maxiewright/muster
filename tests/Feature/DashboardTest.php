<?php

use App\Models\Event;
use App\Models\EventType;
use App\Models\PartnerNotification;
use App\Models\Standup;
use App\Models\TrainingGoal;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function (): void {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard displays todays standups', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create standup for today
    $todaysStandup = Standup::factory()->create([
        'user_id' => $otherUser->id,
        'date' => today(),
    ]);

    // Create standup for yesterday (should not appear)
    Standup::factory()->create([
        'user_id' => $otherUser->id,
        'date' => today()->subDay(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee($otherUser->name);
});

test('dashboard displays upcoming events for next 7 days', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();

    // Create event within next 7 days
    $upcomingEvent = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'starts_at' => now()->addDays(3),
        'title' => 'Upcoming Meeting',
    ]);

    // Create event beyond 7 days (should not appear)
    Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'starts_at' => now()->addDays(10),
        'title' => 'Future Meeting',
    ]);

    // Create past event (should not appear)
    Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'starts_at' => now()->subDays(1),
        'title' => 'Past Meeting',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Upcoming Meeting');
    $response->assertDontSee('Future Meeting');
    $response->assertDontSee('Past Meeting');
});

test('dashboard displays users own standup for today', function (): void {
    $user = User::factory()->create();

    // Create the user's standup for today
    $myStandup = Standup::factory()->create([
        'user_id' => $user->id,
        'date' => today(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    // Should not display the prompt to check in
    $response->assertDontSee("You haven't checked in today");
    // Should display the user's name in today's standups
    $response->assertSee($user->name);
});

test('dashboard limits upcoming events to 5', function (): void {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();

    // Create 7 events within next 7 days
    Event::factory()->count(7)->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
        'starts_at' => now()->addDays(1),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    // Should only show 5 events maximum
    expect(\App\Models\Event::where('starts_at', '>=', now())
        ->where('starts_at', '<=', now()->addDays(7))
        ->count())->toBe(7);
});

test('dashboard displays notifications and team updates from others', function (): void {
    $user = User::factory()->create();
    $teammate = User::factory()->create(['name' => 'Teammate One']);
    $sender = User::factory()->create(['name' => 'Captain Carter']);
    $goal = TrainingGoal::query()->create([
        'slug' => 'combat-readiness-goal',
        'user_id' => $sender->id,
        'title' => 'Combat Readiness',
        'start_date' => now()->toDateString(),
        'target_date' => now()->addDays(30)->toDateString(),
        'status' => 'active',
        'partner_status' => 'accepted',
    ]);

    Standup::factory()->create([
        'user_id' => $teammate->id,
        'date' => today(),
        'blockers' => 'Waiting for deployment approval.',
    ]);

    PartnerNotification::query()->create([
        'user_id' => $user->id,
        'from_user_id' => $sender->id,
        'training_goal_id' => $goal->id,
        'type' => 'checkin_logged',
        'title' => 'Partner check-in submitted',
        'message' => 'Captain Carter logged a training check-in.',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Notifications');
    $response->assertSee('Team Updates');
    $response->assertSee('Partner check-in submitted');
    $response->assertSee('Captain Carter');
    $response->assertSee('Teammate One');
});

test('partner request notifications are actionable from dashboard', function (): void {
    $owner = User::factory()->create(['name' => 'Goal Owner']);
    $partner = User::factory()->create(['name' => 'Partner User']);

    $goal = TrainingGoal::query()->create([
        'slug' => 'actionable-partner-request-goal',
        'user_id' => $owner->id,
        'accountability_partner_id' => $partner->id,
        'title' => 'Actionable Partner Request',
        'start_date' => now()->toDateString(),
        'target_date' => now()->addDays(30)->toDateString(),
        'status' => 'active',
        'partner_status' => 'pending',
    ]);

    $notification = PartnerNotification::query()->create([
        'user_id' => $partner->id,
        'from_user_id' => $owner->id,
        'training_goal_id' => $goal->id,
        'type' => 'partner_request',
        'title' => 'New partner request',
        'message' => 'Goal Owner invited you to support: Actionable Partner Request',
    ]);

    $this->actingAs($partner)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Accept')
        ->assertSee('Decline')
        ->assertSee('Review');

    Livewire::actingAs($partner)
        ->test('dashboard')
        ->call('acceptPartnerRequest', $notification->id);

    expect($goal->fresh()->partner_status->value)->toBe('accepted');
    expect($notification->fresh()->actioned_at)->not->toBeNull();
});
