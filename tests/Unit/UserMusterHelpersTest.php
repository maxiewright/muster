<?php

declare(strict_types=1);

use App\Models\Muster;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function attachUserMusterHelperUserToUnit(User $user, Organization $organization, Unit $unit, string $role = 'member'): void
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

test('user can look up a muster for a specific date within a unit', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $user = User::factory()->create(['organization_id' => $organization->id]);

    attachUserMusterHelperUserToUnit($user, $organization, $alphaUnit);
    attachUserMusterHelperUserToUnit($user, $organization, $bravoUnit);

    $alphaMuster = Muster::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $alphaUnit->id,
        'date' => today()->subDay(),
    ]);

    Muster::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $bravoUnit->id,
        'date' => today()->subDay(),
    ]);

    expect($user->musterForDate(today()->subDay(), $alphaUnit->id)?->is($alphaMuster))->toBeTrue();
});

test('user can look up the previous muster before a date within a unit', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $user = User::factory()->create(['organization_id' => $organization->id]);

    attachUserMusterHelperUserToUnit($user, $organization, $alphaUnit);
    attachUserMusterHelperUserToUnit($user, $organization, $bravoUnit);

    Muster::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $alphaUnit->id,
        'date' => today()->subDays(3),
    ]);

    $expectedMuster = Muster::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $alphaUnit->id,
        'date' => today()->subDay(),
    ]);

    Muster::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $bravoUnit->id,
        'date' => today()->subDays(2),
    ]);

    expect($user->previousMuster(today(), $alphaUnit->id)?->is($expectedMuster))->toBeTrue();
});
