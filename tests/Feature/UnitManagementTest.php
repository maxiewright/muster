<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;

use function Pest\Laravel\actingAs;

function attachUnitManagementUserToUnit(User $user, Organization $organization, Unit $unit, string $role = 'owner'): void
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

test('organization leads can create units in their organization', function (): void {
    $organization = Organization::factory()->create(['name' => 'Regiment']);
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $lead = User::factory()->lead()->create(['organization_id' => $organization->id]);

    attachUnitManagementUserToUnit($lead, $organization, $alphaUnit);

    actingAs($lead)
        ->withSession(['active_unit_id' => $alphaUnit->id])
        ->post(route('team.units.store'), [
            'name' => 'Bravo Unit',
        ])
        ->assertRedirect(route('team.units.index'));

    $newUnit = Unit::query()
        ->where('organization_id', $organization->id)
        ->where('name', 'Bravo Unit')
        ->first();

    expect($newUnit)->not->toBeNull();
    expect(UnitMembership::query()
        ->where('user_id', $lead->id)
        ->where('unit_id', $newUnit?->id)
        ->where('role', 'owner')
        ->exists())->toBeTrue();

    expect(session('active_unit_id'))->toBe($newUnit?->id);
});

test('members cannot create units', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create();
    $member = User::factory()->create(['organization_id' => $organization->id, 'role' => Role::Member]);

    attachUnitManagementUserToUnit($member, $organization, $alphaUnit, 'member');

    actingAs($member)
        ->post(route('team.units.store'), [
            'name' => 'Bravo Unit',
        ])
        ->assertForbidden();

    expect(Unit::query()->where('organization_id', $organization->id)->count())->toBe(1);
});

test('unit management only lists units from the current organization', function (): void {
    $firstOrganization = Organization::factory()->create(['name' => 'Regiment']);
    $secondOrganization = Organization::factory()->create(['name' => 'Air Guard']);
    $alphaUnit = Unit::factory()->for($firstOrganization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($firstOrganization)->create(['name' => 'Bravo Unit']);
    $otherOrganizationUnit = Unit::factory()->for($secondOrganization)->create(['name' => 'Flight Unit']);
    $lead = User::factory()->lead()->create(['organization_id' => $firstOrganization->id]);

    attachUnitManagementUserToUnit($lead, $firstOrganization, $alphaUnit);

    actingAs($lead)
        ->get(route('team.units.index'))
        ->assertOk()
        ->assertSee('Alpha Unit')
        ->assertSee('Bravo Unit')
        ->assertDontSee('Flight Unit');
});
