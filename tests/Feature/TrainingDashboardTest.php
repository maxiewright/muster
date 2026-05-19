<?php

declare(strict_types=1);

use App\Enums\UnitMembershipRole;
use App\Models\Organization;
use App\Models\TrainingGoal;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;

function attachTrainingDashboardUserToUnit(User $user, Organization $organization, Unit $unit): void
{
    $user->forceFill(['organization_id' => $organization->id])->save();

    UnitMembership::query()->firstOrCreate([
        'user_id' => $user->id,
        'unit_id' => $unit->id,
    ], [
        'role' => UnitMembershipRole::Member,
    ]);
}

it('only shows training goals from the active unit on the dashboard', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $user = User::factory()->create(['organization_id' => $organization->id]);

    attachTrainingDashboardUserToUnit($user, $organization, $alphaUnit);
    attachTrainingDashboardUserToUnit($user, $organization, $bravoUnit);

    TrainingGoal::factory()->for($user)->active()->create([
        'organization_id' => $organization->id,
        'unit_id' => $alphaUnit->id,
        'title' => 'Alpha Goal',
    ]);

    TrainingGoal::factory()->for($user)->active()->create([
        'organization_id' => $organization->id,
        'unit_id' => $bravoUnit->id,
        'title' => 'Bravo Goal',
    ]);

    $this->actingAs($user)
        ->withSession(['active_unit_id' => $alphaUnit->id])
        ->get(route('training.dashboard'))
        ->assertOk()
        ->assertSee('Alpha Goal')
        ->assertDontSee('Bravo Goal');
});

it('shows the commander training assignment entry point to lead users only', function (): void {
    $organization = Organization::factory()->create();
    $unit = Unit::factory()->for($organization)->create();
    $lead = User::factory()->lead()->create(['organization_id' => $organization->id]);
    $member = User::factory()->create(['organization_id' => $organization->id]);

    attachTrainingDashboardUserToUnit($lead, $organization, $unit);
    attachTrainingDashboardUserToUnit($member, $organization, $unit);

    $this->actingAs($lead)
        ->withSession(['active_unit_id' => $unit->id])
        ->get(route('training.dashboard'))
        ->assertOk()
        ->assertSee('Assign Training');

    $this->actingAs($member)
        ->withSession(['active_unit_id' => $unit->id])
        ->get(route('training.dashboard'))
        ->assertOk()
        ->assertDontSee('Assign Training');
});
