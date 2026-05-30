<?php

use App\Models\Event;
use App\Models\EventType;
use App\Models\Muster;
use App\Models\Organization;
use App\Models\PartnerNotification;
use App\Models\TrainingGoal;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Livewire\Livewire;

function attachUserToUnit(User $user, Organization $organization, Unit $unit, string $role = 'member'): void
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

test('dashboard layout exposes the operational navigation in the requested order', function (): void {
    $organization = Organization::query()->create(['name' => 'Ops', 'slug' => 'ops']);
    $unit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Alpha', 'slug' => 'alpha']);
    $user = User::factory()->lead()->create(['organization_id' => $organization->id]);
    attachUserToUnit($user, $organization, $unit, 'commander');

    $response = $this->actingAs($user)
        ->withSession(['active_unit_id' => $unit->id])
        ->get(route('dashboard'));

    $response->assertOk();

    $content = $response->getContent();

    expect(strpos($content, 'href="'.route('musters').'"'))->toBeLessThan(strpos($content, 'href="'.route('missions.index').'"'));
    expect(strpos($content, 'href="'.route('missions.index').'"'))->toBeLessThan(strpos($content, 'href="'.route('tasks').'"'));
    expect(strpos($content, 'href="'.route('tasks').'"'))->toBeLessThan(strpos($content, 'href="'.route('training.dashboard').'"'));
    expect(strpos($content, 'href="'.route('training.dashboard').'"'))->toBeLessThan(strpos($content, 'href="'.route('calendar').'"'));
});

test('dashboard displays todays musters', function (): void {
    $organization = Organization::query()->create(['name' => 'Ops', 'slug' => 'ops']);
    $unit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Alpha', 'slug' => 'alpha']);
    $otherUnit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Bravo', 'slug' => 'bravo']);
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    attachUserToUnit($user, $organization, $unit);
    attachUserToUnit($otherUser, $organization, $unit);
    attachUserToUnit($otherUser, $organization, $otherUnit);

    Muster::factory()->create([
        'user_id' => $otherUser->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'date' => today(),
    ]);

    Muster::factory()->create([
        'user_id' => $otherUser->id,
        'organization_id' => $organization->id,
        'unit_id' => $otherUnit->id,
        'date' => today(),
    ]);

    Muster::factory()->create([
        'user_id' => $otherUser->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'date' => today()->subDay(),
    ]);

    $this->actingAs($user)->withSession(['active_unit_id' => $unit->id]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee($otherUser->name);
});

test('dashboard displays upcoming events for next 7 days', function (): void {
    $organization = Organization::query()->create(['name' => 'Ops', 'slug' => 'ops']);
    $unit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Alpha', 'slug' => 'alpha']);
    $otherUnit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Bravo', 'slug' => 'bravo']);
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();
    attachUserToUnit($user, $organization, $unit);

    Event::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'event_type_id' => $eventType->id,
        'starts_at' => now()->addDays(3),
        'title' => 'Upcoming Meeting',
    ]);

    Event::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'event_type_id' => $eventType->id,
        'starts_at' => now()->addDays(10),
        'title' => 'Future Meeting',
    ]);

    Event::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'event_type_id' => $eventType->id,
        'starts_at' => now()->subDays(1),
        'title' => 'Past Meeting',
    ]);

    Event::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $otherUnit->id,
        'event_type_id' => $eventType->id,
        'starts_at' => now()->addDays(2),
        'title' => 'Other Unit Meeting',
    ]);

    $this->actingAs($user)->withSession(['active_unit_id' => $unit->id]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Upcoming Meeting');
    $response->assertDontSee('Other Unit Meeting');
    $response->assertDontSee('Future Meeting');
    $response->assertDontSee('Past Meeting');
});

test('dashboard displays users own muster for today', function (): void {
    $organization = Organization::query()->create(['name' => 'Ops', 'slug' => 'ops']);
    $unit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Alpha', 'slug' => 'alpha']);
    $user = User::factory()->create();
    attachUserToUnit($user, $organization, $unit);

    Muster::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'date' => today(),
    ]);

    $this->actingAs($user)->withSession(['active_unit_id' => $unit->id]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    // Should not display the prompt to check in
    $response->assertDontSee("You haven't checked in today");
    // Should display the user's name in today's musters
    $response->assertSee($user->name);
});

test('dashboard limits upcoming events to 5', function (): void {
    $organization = Organization::query()->create(['name' => 'Ops', 'slug' => 'ops']);
    $unit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Alpha', 'slug' => 'alpha']);
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();
    attachUserToUnit($user, $organization, $unit);

    Event::factory()->count(7)->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'event_type_id' => $eventType->id,
        'starts_at' => now()->addDays(1),
    ]);

    $this->actingAs($user)->withSession(['active_unit_id' => $unit->id]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    // Should only show 5 events maximum
    expect(Event::where('starts_at', '>=', now())
        ->where('starts_at', '<=', now()->addDays(7))
        ->count())->toBe(7);
});

test('dashboard displays notifications and team updates from others', function (): void {
    $organization = Organization::query()->create(['name' => 'Ops', 'slug' => 'ops']);
    $unit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Alpha', 'slug' => 'alpha']);
    $user = User::factory()->create();
    $teammate = User::factory()->create(['name' => 'Teammate One']);
    $sender = User::factory()->create(['name' => 'Captain Carter']);
    attachUserToUnit($user, $organization, $unit);
    attachUserToUnit($teammate, $organization, $unit);
    attachUserToUnit($sender, $organization, $unit);
    $goal = TrainingGoal::query()->create([
        'slug' => 'combat-readiness-goal',
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'user_id' => $sender->id,
        'title' => 'Combat Readiness',
        'start_date' => now()->toDateString(),
        'target_date' => now()->addDays(30)->toDateString(),
        'status' => 'active',
        'partner_status' => 'accepted',
    ]);

    Muster::factory()->create([
        'user_id' => $teammate->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'date' => today(),
        'blockers' => 'Waiting for deployment approval.',
    ]);

    PartnerNotification::query()->create([
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'user_id' => $user->id,
        'from_user_id' => $sender->id,
        'training_goal_id' => $goal->id,
        'type' => 'checkin_logged',
        'title' => 'Partner check-in submitted',
        'message' => 'Captain Carter logged a training check-in.',
    ]);

    $this->actingAs($user)->withSession(['active_unit_id' => $unit->id]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Intel Feed');
    $response->assertSee('Squad Comms');
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
