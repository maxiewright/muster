<?php

use App\Models\Event;
use App\Models\EventType;
use App\Models\Organization;
use App\Models\Task;
use App\Models\TrainingGoal;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;

function attachCalendarUserToUnit(User $user, Organization $organization, Unit $unit, string $role = 'member'): void
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
    $organization = Organization::query()->create(['name' => 'Ops', 'slug' => 'ops']);
    $unit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Alpha', 'slug' => 'alpha']);
    $otherUnit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Bravo', 'slug' => 'bravo']);
    $user = User::factory()->create();
    $eventType = EventType::factory()->create();
    attachCalendarUserToUnit($user, $organization, $unit);

    Event::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'event_type_id' => $eventType->id,
        'title' => 'Current Month Event',
        'starts_at' => now()->startOfMonth()->addDays(5),
    ]);

    Event::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'event_type_id' => $eventType->id,
        'title' => 'Next Month Event',
        'starts_at' => now()->addMonth()->startOfMonth()->addDays(5),
    ]);

    Event::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $otherUnit->id,
        'event_type_id' => $eventType->id,
        'title' => 'Other Unit Event',
        'starts_at' => now()->startOfMonth()->addDays(6),
    ]);

    $this->actingAs($user)->withSession(['active_unit_id' => $unit->id]);

    $response = $this->get(route('calendar'));

    $response->assertSuccessful();
    $response->assertSee('Current Month Event');
    $response->assertDontSee('Other Unit Event');
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

test('calendar shows events from the active unit only', function (): void {
    $organization = Organization::query()->create(['name' => 'Ops', 'slug' => 'ops']);
    $unit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Alpha', 'slug' => 'alpha']);
    $otherUnit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Bravo', 'slug' => 'bravo']);
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $eventType = EventType::factory()->create();
    attachCalendarUserToUnit($user1, $organization, $unit);
    attachCalendarUserToUnit($user2, $organization, $unit);
    attachCalendarUserToUnit($user2, $organization, $otherUnit);

    Event::factory()->create([
        'user_id' => $user1->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'event_type_id' => $eventType->id,
        'title' => 'User 1 Event',
        'starts_at' => now()->startOfMonth()->addDays(5),
    ]);

    Event::factory()->create([
        'user_id' => $user2->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'event_type_id' => $eventType->id,
        'title' => 'User 2 Event',
        'starts_at' => now()->startOfMonth()->addDays(6),
    ]);

    Event::factory()->create([
        'user_id' => $user2->id,
        'organization_id' => $organization->id,
        'unit_id' => $otherUnit->id,
        'event_type_id' => $eventType->id,
        'title' => 'Other Unit Event',
        'starts_at' => now()->startOfMonth()->addDays(7),
    ]);

    $this->actingAs($user1)->withSession(['active_unit_id' => $unit->id]);

    $response = $this->get(route('calendar'));

    $response->assertSuccessful();
    $response->assertSee('User 1 Event');
    $response->assertSee('User 2 Event');
    $response->assertDontSee('Other Unit Event');
});

test('calendar shows actions events and planned training for the active unit', function (): void {
    $organization = Organization::query()->create(['name' => 'Ops', 'slug' => 'ops']);
    $unit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Alpha', 'slug' => 'alpha']);
    $otherUnit = Unit::query()->create(['organization_id' => $organization->id, 'name' => 'Bravo', 'slug' => 'bravo']);
    $user = User::factory()->lead()->create(['organization_id' => $organization->id]);
    $eventType = EventType::factory()->create();
    attachCalendarUserToUnit($user, $organization, $unit, 'commander');
    attachCalendarUserToUnit($user, $organization, $otherUnit, 'commander');

    Event::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'event_type_id' => $eventType->id,
        'title' => 'Operations Brief',
        'starts_at' => now()->startOfMonth()->addDays(4)->setTime(9, 0),
    ]);

    Task::factory()->create([
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'created_by' => $user->id,
        'assigned_to' => $user->id,
        'title' => 'Secure Perimeter',
        'due_date' => now()->startOfMonth()->addDays(5)->toDateString(),
    ]);

    TrainingGoal::query()->create([
        'slug' => 'marksmanship-refresh',
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'user_id' => $user->id,
        'title' => 'Marksmanship Refresh',
        'start_date' => now()->toDateString(),
        'target_date' => now()->startOfMonth()->addDays(6)->toDateString(),
        'status' => 'active',
        'partner_status' => 'none',
        'is_unit_directed' => true,
    ]);

    Task::factory()->create([
        'organization_id' => $organization->id,
        'unit_id' => $otherUnit->id,
        'created_by' => $user->id,
        'assigned_to' => $user->id,
        'title' => 'Hidden Action',
        'due_date' => now()->startOfMonth()->addDays(7)->toDateString(),
    ]);

    TrainingGoal::query()->create([
        'slug' => 'hidden-training',
        'organization_id' => $organization->id,
        'unit_id' => $otherUnit->id,
        'user_id' => $user->id,
        'title' => 'Hidden Training',
        'start_date' => now()->toDateString(),
        'target_date' => now()->startOfMonth()->addDays(8)->toDateString(),
        'status' => 'active',
        'partner_status' => 'none',
        'is_unit_directed' => true,
    ]);

    $this->actingAs($user)->withSession(['active_unit_id' => $unit->id]);

    $response = $this->get(route('calendar'));

    $response->assertSuccessful();
    $response->assertSee('Operational Calendar');
    $response->assertSee('Operations Brief');
    $response->assertSee('Secure Perimeter');
    $response->assertSee('Marksmanship Refresh');
    $response->assertDontSee('Hidden Action');
    $response->assertDontSee('Hidden Training');
});
