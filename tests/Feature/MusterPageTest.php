<?php

use App\Enums\UnitMembershipRole;
use App\Models\Muster;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function attachMusterPageUserToUnit(User $user, Organization $organization, Unit $unit): void
{
    $user->forceFill(['organization_id' => $organization->id])->save();

    UnitMembership::query()->firstOrCreate([
        'user_id' => $user->id,
        'unit_id' => $unit->id,
    ], [
        'role' => UnitMembershipRole::Member,
    ]);
}

it('loads the muster page and shows the step heading', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('muster.create'));

    $response->assertOk();
    $response->assertSeeText('What did you work on yesterday?');
});

it('shows only active unit musters on the board', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $alphaUser = User::factory()->create(['name' => 'Alpha Operator', 'organization_id' => $organization->id]);
    $bravoUser = User::factory()->create(['name' => 'Bravo Operator', 'organization_id' => $organization->id]);
    $viewer = User::factory()->create(['organization_id' => $organization->id]);

    attachMusterPageUserToUnit($viewer, $organization, $alphaUnit);
    attachMusterPageUserToUnit($viewer, $organization, $bravoUnit);
    attachMusterPageUserToUnit($alphaUser, $organization, $alphaUnit);
    attachMusterPageUserToUnit($bravoUser, $organization, $bravoUnit);

    Muster::factory()->create([
        'user_id' => $alphaUser->id,
        'organization_id' => $organization->id,
        'unit_id' => $alphaUnit->id,
        'date' => today(),
    ]);

    Muster::factory()->create([
        'user_id' => $bravoUser->id,
        'organization_id' => $organization->id,
        'unit_id' => $bravoUnit->id,
        'date' => today(),
    ]);

    $this->actingAs($viewer)
        ->withSession(['active_unit_id' => $alphaUnit->id])
        ->get(route('musters'))
        ->assertOk()
        ->assertSee('Alpha Operator')
        ->assertDontSee('Bravo Operator');
});

it('exposes the active unit board entries through the musters collection', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $alphaUser = User::factory()->create(['name' => 'Alpha Operator', 'organization_id' => $organization->id]);
    $bravoUser = User::factory()->create(['name' => 'Bravo Operator', 'organization_id' => $organization->id]);
    $viewer = User::factory()->create(['organization_id' => $organization->id]);

    attachMusterPageUserToUnit($viewer, $organization, $alphaUnit);
    attachMusterPageUserToUnit($viewer, $organization, $bravoUnit);
    attachMusterPageUserToUnit($alphaUser, $organization, $alphaUnit);
    attachMusterPageUserToUnit($bravoUser, $organization, $bravoUnit);

    $visibleMuster = Muster::factory()->create([
        'user_id' => $alphaUser->id,
        'organization_id' => $organization->id,
        'unit_id' => $alphaUnit->id,
        'date' => today(),
    ]);

    Muster::factory()->create([
        'user_id' => $bravoUser->id,
        'organization_id' => $organization->id,
        'unit_id' => $bravoUnit->id,
        'date' => today(),
    ]);

    $this->actingAs($viewer)->withSession(['active_unit_id' => $alphaUnit->id]);

    $musters = Livewire::test('muster.muster-board')->get('musters');

    expect($musters->pluck('id')->all())->toBe([$visibleMuster->id]);
});

it('exposes the active unit board entries through the canonical muster board alias', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $alphaUser = User::factory()->create(['name' => 'Alpha Operator', 'organization_id' => $organization->id]);
    $bravoUser = User::factory()->create(['name' => 'Bravo Operator', 'organization_id' => $organization->id]);
    $viewer = User::factory()->create(['organization_id' => $organization->id]);

    attachMusterPageUserToUnit($viewer, $organization, $alphaUnit);
    attachMusterPageUserToUnit($viewer, $organization, $bravoUnit);
    attachMusterPageUserToUnit($alphaUser, $organization, $alphaUnit);
    attachMusterPageUserToUnit($bravoUser, $organization, $bravoUnit);

    $visibleMuster = Muster::factory()->create([
        'user_id' => $alphaUser->id,
        'organization_id' => $organization->id,
        'unit_id' => $alphaUnit->id,
        'date' => today(),
    ]);

    Muster::factory()->create([
        'user_id' => $bravoUser->id,
        'organization_id' => $organization->id,
        'unit_id' => $bravoUnit->id,
        'date' => today(),
    ]);

    $this->actingAs($viewer)->withSession(['active_unit_id' => $alphaUnit->id]);

    $musters = Livewire::test('muster.muster-board')->get('musters');

    expect($musters->pluck('id')->all())->toBe([$visibleMuster->id]);
});

it('exposes the viewers own board entry through the canonical myMuster property', function (): void {
    $organization = Organization::factory()->create();
    $unit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $user = User::factory()->create(['organization_id' => $organization->id]);

    attachMusterPageUserToUnit($user, $organization, $unit);

    $muster = Muster::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'unit_id' => $unit->id,
        'date' => today(),
    ]);

    $this->actingAs($user)->withSession(['active_unit_id' => $unit->id]);

    $component = Livewire::test('muster.muster-board');

    expect($component->get('myMuster')?->is($muster))->toBeTrue();
});
