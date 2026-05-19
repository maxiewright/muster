<?php

declare(strict_types=1);

use App\Enums\ConfidenceLevel;
use App\Enums\MilestoneStatus;
use App\Enums\UnitMembershipRole;
use App\Livewire\Training\TrainingCheckinForm;
use App\Models\Organization;
use App\Models\TrainingGoal;
use App\Models\TrainingMilestone;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

function attachTrainingCheckinUserToUnit(User $user, Organization $organization, Unit $unit): void
{
    $user->forceFill(['organization_id' => $organization->id])->save();

    UnitMembership::query()->firstOrCreate([
        'user_id' => $user->id,
        'unit_id' => $unit->id,
    ], [
        'role' => UnitMembershipRole::Member,
    ]);
}

it('forbids completing a milestone from a different goal during checkin', function (): void {
    $organization = Organization::factory()->create();
    $unit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $user = User::factory()->create(['organization_id' => $organization->id]);

    attachTrainingCheckinUserToUnit($user, $organization, $unit);

    $goal = TrainingGoal::factory()->for($user)->active()->create([
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
    ]);
    $otherGoal = TrainingGoal::factory()->for($user)->active()->create([
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
    ]);
    $foreignMilestone = TrainingMilestone::factory()->for($otherGoal, 'goal')->create([
        'status' => MilestoneStatus::Pending,
    ]);

    actingAs($user);
    session(['active_unit_id' => $unit->id]);

    Livewire::test(TrainingCheckinForm::class, ['goal' => $goal])
        ->set('progress_update', 'Worked through the material.')
        ->set('minutes_logged', 30)
        ->set('confidence_level', ConfidenceLevel::OnTrack->value)
        ->set('milestone_id', $foreignMilestone->id)
        ->call('save')
        ->assertHasErrors('milestone_id');

    expect($foreignMilestone->fresh()->status)->toBe(MilestoneStatus::Pending);
});

it('forbids opening a training checkin form for a goal outside the active unit', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $user = User::factory()->create(['organization_id' => $organization->id]);

    attachTrainingCheckinUserToUnit($user, $organization, $alphaUnit);
    attachTrainingCheckinUserToUnit($user, $organization, $bravoUnit);

    $goal = TrainingGoal::factory()->for($user)->active()->create([
        'organization_id' => $organization->id,
        'unit_id' => $bravoUnit->id,
    ]);

    actingAs($user);
    session(['active_unit_id' => $alphaUnit->id]);

    Livewire::test(TrainingCheckinForm::class, ['goal' => $goal])
        ->assertForbidden();
});
