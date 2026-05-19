<?php

declare(strict_types=1);

use App\Enums\TrainingCategory;
use App\Enums\UnitMembershipRole;
use App\Livewire\Training\TrainingGoalForm;
use App\Models\FocusArea;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Livewire\Livewire;

function attachTrainingGoalFormUserToUnit(User $user, Organization $organization, Unit $unit): void
{
    $user->forceFill(['organization_id' => $organization->id])->save();

    UnitMembership::query()->firstOrCreate([
        'user_id' => $user->id,
        'unit_id' => $unit->id,
    ], [
        'role' => UnitMembershipRole::Member,
    ]);
}

it('loads training goal create page for authenticated users', function (): void {
    $user = User::factory()->create();
    FocusArea::factory()->create();

    $this->actingAs($user)
        ->get(route('training.goals.create'))
        ->assertOk()
        ->assertSee('Set a New Training Goal');
});

it('rejects selecting an accountability partner outside the active unit', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $outOfUnitPartner = User::factory()->create(['organization_id' => $organization->id]);
    $focusArea = FocusArea::factory()->create();

    attachTrainingGoalFormUserToUnit($user, $organization, $alphaUnit);
    attachTrainingGoalFormUserToUnit($outOfUnitPartner, $organization, $bravoUnit);

    $this->actingAs($user);
    session(['active_unit_id' => $alphaUnit->id]);

    Livewire::test(TrainingGoalForm::class)
        ->set('title', 'Tenant-safe goal')
        ->set('category', TrainingCategory::Technical->value)
        ->set('focus_area_id', $focusArea->id)
        ->set('start_date', now()->toDateString())
        ->set('target_date', now()->addWeek()->toDateString())
        ->set('description', 'Keep training scoped to the active unit.')
        ->set('success_criteria', 'Only active-unit members can be selected as partners.')
        ->set('accountability_partner_id', $outOfUnitPartner->id)
        ->call('save')
        ->assertHasErrors('accountability_partner_id');
});
