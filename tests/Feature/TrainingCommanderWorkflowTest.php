<?php

declare(strict_types=1);

use App\Enums\PartnerStatus;
use App\Enums\TrainingCategory;
use App\Livewire\Training\TrainingAssignmentManager;
use App\Models\FocusArea;
use App\Models\Organization;
use App\Models\TrainingGoal;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Livewire\Livewire;

function attachTrainingCommanderUserToUnit(User $user, Organization $organization, Unit $unit, string $role = 'member'): void
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

test('leads can assign unit-directed training goals to selected members', function (): void {
    $focusArea = FocusArea::factory()->create();
    $organization = Organization::factory()->create();
    $unit = Unit::factory()->for($organization)->create();
    $lead = User::factory()->lead()->create(['organization_id' => $organization->id]);
    $firstMember = User::factory()->create(['organization_id' => $organization->id]);
    $secondMember = User::factory()->create(['organization_id' => $organization->id]);
    $partner = User::factory()->create(['organization_id' => $organization->id]);

    attachTrainingCommanderUserToUnit($lead, $organization, $unit, 'commander');
    attachTrainingCommanderUserToUnit($firstMember, $organization, $unit);
    attachTrainingCommanderUserToUnit($secondMember, $organization, $unit);
    attachTrainingCommanderUserToUnit($partner, $organization, $unit);

    $this->actingAs($lead)->withSession(['active_unit_id' => $unit->id]);

    Livewire::actingAs($lead)
        ->test(TrainingAssignmentManager::class)
        ->set('title', 'CQB refresher')
        ->set('description', 'Refresh close quarters battle fundamentals.')
        ->set('success_criteria', 'Complete the assessment and live drill.')
        ->set('category', TrainingCategory::Technical->value)
        ->set('focus_area_id', $focusArea->id)
        ->set('selected_member_ids', [$firstMember->id, $secondMember->id])
        ->set('partner_policy', 'commander_locked')
        ->set('accountability_partner_id', $partner->id)
        ->call('save')
        ->assertRedirect(route('training.dashboard'));

    $goals = TrainingGoal::query()->where('title', 'CQB refresher')->orderBy('user_id')->get();

    expect($goals)->toHaveCount(2);
    expect($goals->pluck('user_id')->all())->toBe([$firstMember->id, $secondMember->id]);
    expect($goals->every(fn (TrainingGoal $goal): bool => $goal->is_unit_directed))->toBeTrue();
    expect($goals->every(fn (TrainingGoal $goal): bool => $goal->assigned_by_user_id === $lead->id))->toBeTrue();
    expect($goals->every(fn (TrainingGoal $goal): bool => $goal->accountability_partner_required))->toBeTrue();
    expect($goals->every(fn (TrainingGoal $goal): bool => $goal->accountability_partner_locked))->toBeTrue();
    expect($goals->every(fn (TrainingGoal $goal): bool => $goal->accountability_partner_id === $partner->id))->toBeTrue();
    expect($goals->every(fn (TrainingGoal $goal): bool => $goal->partner_status === PartnerStatus::Accepted))->toBeTrue();
});

test('select all with exclusions only assigns training to the remaining unit members', function (): void {
    $focusArea = FocusArea::factory()->create();
    $organization = Organization::factory()->create();
    $unit = Unit::factory()->for($organization)->create();
    $lead = User::factory()->lead()->create(['organization_id' => $organization->id]);
    $firstMember = User::factory()->create(['organization_id' => $organization->id]);
    $secondMember = User::factory()->create(['organization_id' => $organization->id]);
    $excludedMember = User::factory()->create(['organization_id' => $organization->id]);

    attachTrainingCommanderUserToUnit($lead, $organization, $unit, 'commander');
    attachTrainingCommanderUserToUnit($firstMember, $organization, $unit);
    attachTrainingCommanderUserToUnit($secondMember, $organization, $unit);
    attachTrainingCommanderUserToUnit($excludedMember, $organization, $unit);

    $this->actingAs($lead)->withSession(['active_unit_id' => $unit->id]);

    Livewire::actingAs($lead)
        ->test(TrainingAssignmentManager::class)
        ->set('title', 'Readiness drill')
        ->set('description', 'Complete the unit readiness drill package.')
        ->set('success_criteria', 'Pass drill requirements and submit evidence.')
        ->set('category', TrainingCategory::Course->value)
        ->set('focus_area_id', $focusArea->id)
        ->set('assign_to_all_members', true)
        ->set('excluded_member_ids', [$excludedMember->id])
        ->set('partner_policy', 'member_required')
        ->call('save')
        ->assertRedirect(route('training.dashboard'));

    $goals = TrainingGoal::query()->where('title', 'Readiness drill')->orderBy('user_id')->get();

    expect($goals)->toHaveCount(2);
    expect($goals->pluck('user_id')->all())->toBe([$firstMember->id, $secondMember->id]);
    expect($goals->every(fn (TrainingGoal $goal): bool => $goal->accountability_partner_required))->toBeTrue();
    expect($goals->every(fn (TrainingGoal $goal): bool => ! $goal->accountability_partner_locked))->toBeTrue();
    expect($goals->every(fn (TrainingGoal $goal): bool => $goal->accountability_partner_id === null))->toBeTrue();
});

test('non-leads cannot access the commander training assignment screen', function (): void {
    $organization = Organization::factory()->create();
    $unit = Unit::factory()->for($organization)->create();
    $member = User::factory()->create(['organization_id' => $organization->id]);

    attachTrainingCommanderUserToUnit($member, $organization, $unit);

    $this->actingAs($member)
        ->withSession(['active_unit_id' => $unit->id])
        ->get(route('training.assignments'))
        ->assertForbidden();
});
