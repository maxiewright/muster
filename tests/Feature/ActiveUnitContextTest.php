<?php

use App\Models\Organization;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;

test('authenticated users get an active unit stored in session from their first membership', function (): void {
    $organization = Organization::query()->create([
        'name' => 'Operations Group',
        'slug' => 'operations-group',
    ]);

    $user = User::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $firstUnit = Unit::query()->create([
        'organization_id' => $organization->id,
        'name' => 'Alpha Company',
        'slug' => 'alpha-company',
    ]);

    $secondUnit = Unit::query()->create([
        'organization_id' => $organization->id,
        'name' => 'Bravo Company',
        'slug' => 'bravo-company',
    ]);

    UnitMembership::query()->create([
        'user_id' => $user->id,
        'unit_id' => $secondUnit->id,
        'role' => 'member',
    ]);

    UnitMembership::query()->create([
        'user_id' => $user->id,
        'unit_id' => $firstUnit->id,
        'role' => 'owner',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSessionHas('active_unit_id', $firstUnit->id)
        ->assertSee('Alpha Company');
});
