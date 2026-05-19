<?php

declare(strict_types=1);

use App\Enums\UnitMembershipRole;
use App\Livewire\Training\PartnerNotificationsDropdown;
use App\Models\Organization;
use App\Models\PartnerNotification;
use App\Models\TrainingGoal;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Livewire\Livewire;

function attachPartnerNotificationUserToUnit(User $user, Organization $organization, Unit $unit): void
{
    $user->forceFill(['organization_id' => $organization->id])->save();

    UnitMembership::query()->firstOrCreate([
        'user_id' => $user->id,
        'unit_id' => $unit->id,
    ], [
        'role' => UnitMembershipRole::Member,
    ]);
}

test('shows toast when training checkin notification is received', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PartnerNotificationsDropdown::class)
        ->call('onTrainingCheckinLogged', [
            'from_user_name' => 'Taylor',
            'goal_title' => 'Leadership Sprint',
            'message' => 'Completed two drills.',
        ])
        ->assertDispatched('toast-show');
});

test('shows toast when task assignment notification is received', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PartnerNotificationsDropdown::class)
        ->call('onTaskAssigned', [
            'task' => [
                'title' => 'Prepare muster summary',
                'creator_name' => 'Jordan',
            ],
        ])
        ->assertDispatched('toast-show');
});

test('shows toast when assigned task status changes', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PartnerNotificationsDropdown::class)
        ->call('onTaskStatusChanged', [
            'to_status' => 'completed',
            'changed_by' => 'Jordan',
            'task' => [
                'title' => 'Prepare muster summary',
            ],
        ])
        ->assertDispatched('toast-show');
});

test('only shows partner notifications from the active unit', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $sender = User::factory()->create(['organization_id' => $organization->id]);

    attachPartnerNotificationUserToUnit($user, $organization, $alphaUnit);
    attachPartnerNotificationUserToUnit($user, $organization, $bravoUnit);
    attachPartnerNotificationUserToUnit($sender, $organization, $alphaUnit);
    attachPartnerNotificationUserToUnit($sender, $organization, $bravoUnit);

    $alphaGoal = TrainingGoal::factory()->for($sender)->active()->create([
        'organization_id' => $organization->id,
        'unit_id' => $alphaUnit->id,
        'title' => 'Alpha Training Goal',
    ]);

    $bravoGoal = TrainingGoal::factory()->for($sender)->active()->create([
        'organization_id' => $organization->id,
        'unit_id' => $bravoUnit->id,
        'title' => 'Bravo Training Goal',
    ]);

    PartnerNotification::factory()->create([
        'user_id' => $user->id,
        'from_user_id' => $sender->id,
        'training_goal_id' => $alphaGoal->id,
        'organization_id' => $organization->id,
        'unit_id' => $alphaUnit->id,
        'title' => 'Alpha Notice',
    ]);

    PartnerNotification::factory()->create([
        'user_id' => $user->id,
        'from_user_id' => $sender->id,
        'training_goal_id' => $bravoGoal->id,
        'organization_id' => $organization->id,
        'unit_id' => $bravoUnit->id,
        'title' => 'Bravo Notice',
    ]);

    $this->actingAs($user);
    session(['active_unit_id' => $alphaUnit->id]);

    Livewire::actingAs($user)
        ->test(PartnerNotificationsDropdown::class)
        ->assertSee('Alpha Notice')
        ->assertDontSee('Bravo Notice');
});
