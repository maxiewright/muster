<?php

use App\Enums\Role;
use App\Enums\UnitMembershipRole;
use App\Mail\TeamInvitationMail;
use App\Models\Organization;
use App\Models\TeamInvitation;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;

function attachInvitationTestUserToUnit(User $user, Organization $organization, Unit $unit): void
{
    $user->forceFill(['organization_id' => $organization->id])->save();

    UnitMembership::query()->firstOrCreate([
        'user_id' => $user->id,
        'unit_id' => $unit->id,
    ], [
        'role' => UnitMembershipRole::Member,
    ]);
}

function makeBootstrapInvitation(array $attributes = []): TeamInvitation
{
    return TeamInvitation::query()->create(array_merge([
        'invited_by_user_id' => null,
        'organization_id' => null,
        'unit_id' => null,
        'email' => 'bootstrap.commander@example.com',
        'role' => Role::Lead->value,
        'kind' => TeamInvitation::KIND_BOOTSTRAP,
        'token' => 'bootstrap-token-123',
        'expires_at' => now()->addDays(7),
    ], $attributes));
}

test('visitors are routed to system setup before the platform admin exists', function (): void {
    $this->get(route('home'))
        ->assertRedirect(route('system.setup'));
});

test('visitors see the invite-only landing page after platform setup is complete', function (): void {
    User::factory()->create(['is_platform_admin' => true]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Invite-only operations');
});

test('bootstrap invitation can create the initial organization commander, organization, and first unit', function (): void {
    $invitation = makeBootstrapInvitation();

    $this->get(route('setup', $invitation))
        ->assertOk()
        ->assertSee('Complete Organization Setup')
        ->assertSee($invitation->email);

    $this->post(route('setup.store', $invitation), [
        'organization_name' => 'Engineering Directorate',
        'unit_name' => 'Platform Operations',
        'name' => 'Initial Organization Commander',
        'password' => 'LeadPass123!',
        'password_confirmation' => 'LeadPass123!',
    ])->assertRedirect(route('dashboard'));

    $user = User::query()->first();
    $organization = Organization::query()->first();
    $unit = Unit::query()->first();

    expect($user)->not->toBeNull();
    expect($organization)->not->toBeNull();
    expect($organization?->name)->toBe('Engineering Directorate');
    expect($unit)->not->toBeNull();
    expect($unit?->name)->toBe('Platform Operations');
    expect($user?->email)->toBe($invitation->email);
    expect($user?->organization_id)->toBe($organization?->id);
    expect(UnitMembership::query()->where('user_id', $user?->id)->where('unit_id', $unit?->id)->exists())->toBeTrue();
    expect($invitation->fresh()?->accepted_at)->not->toBeNull();
    $this->assertAuthenticatedAs($user);
});

test('lead can send an email invitation', function (): void {
    Mail::fake();

    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $lead = User::factory()->lead()->create(['organization_id' => $organization->id]);
    attachInvitationTestUserToUnit($lead, $organization, $alphaUnit);
    attachInvitationTestUserToUnit($lead, $organization, $bravoUnit);

    actingAs($lead)
        ->withSession(['active_unit_id' => $bravoUnit->id])
        ->post(route('team.invitations.store'), [
            'email' => 'new.member@example.com',
            'unit_id' => $bravoUnit->id,
            'role' => Role::Member->value,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $invitation = TeamInvitation::query()->first();

    expect($invitation)->not->toBeNull();
    expect($invitation?->email)->toBe('new.member@example.com');
    expect($invitation?->organization_id)->toBe($organization->id);
    expect($invitation?->unit_id)->toBe($bravoUnit->id);

    Mail::assertQueued(TeamInvitationMail::class, 1);
});

test('lead cannot send an email invitation for a unit outside their organization', function (): void {
    Mail::fake();

    $organization = Organization::factory()->create();
    $otherOrganization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $foreignUnit = Unit::factory()->for($otherOrganization)->create(['name' => 'Foreign Unit']);
    $lead = User::factory()->lead()->create(['organization_id' => $organization->id]);
    attachInvitationTestUserToUnit($lead, $organization, $alphaUnit);

    actingAs($lead)
        ->post(route('team.invitations.store'), [
            'email' => 'new.member@example.com',
            'unit_id' => $foreignUnit->id,
            'role' => Role::Member->value,
        ])
        ->assertSessionHasErrors('unit_id');

    expect(TeamInvitation::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

test('invited user can accept invitation and join team', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $lead = User::factory()->lead()->create(['organization_id' => $organization->id]);
    attachInvitationTestUserToUnit($lead, $organization, $alphaUnit);
    attachInvitationTestUserToUnit($lead, $organization, $bravoUnit);

    $invitation = TeamInvitation::query()->create([
        'invited_by_user_id' => $lead->id,
        'organization_id' => $organization->id,
        'unit_id' => $bravoUnit->id,
        'email' => 'joiner@example.com',
        'role' => Role::Member->value,
        'token' => 'invite-token-123',
        'expires_at' => now()->addDays(7),
    ]);

    $this->post(route('invites.accept.store', $invitation), [
        'name' => 'Joiner',
        'password' => 'JoinerPass123!',
        'password_confirmation' => 'JoinerPass123!',
    ])->assertRedirect(route('dashboard'));

    $user = User::query()->where('email', 'joiner@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user?->role)->toBe(Role::Member);
    expect($user?->organization_id)->toBe($organization->id);
    expect(UnitMembership::query()->where('user_id', $user?->id)->where('unit_id', $bravoUnit->id)->exists())->toBeTrue();
    expect(UnitMembership::query()->where('user_id', $user?->id)->where('unit_id', $alphaUnit->id)->exists())->toBeFalse();

    $invitation->refresh();
    expect($invitation->accepted_at)->not->toBeNull();
    $this->assertAuthenticatedAs($user);
});

test('bootstrap setup rejects weak passwords', function (): void {
    $invitation = makeBootstrapInvitation(['token' => 'bootstrap-token-weak-password']);

    $this->from(route('setup', $invitation))
        ->post(route('setup.store', $invitation), [
            'organization_name' => 'Engineering Directorate',
            'unit_name' => 'Platform Operations',
            'name' => 'Initial Director',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('setup', $invitation))
        ->assertSessionHasErrors('password');

    $this->assertGuest();
    expect(User::query()->count())->toBe(0);
});

test('invited user acceptance rejects weak passwords', function (): void {
    $lead = User::factory()->lead()->create();

    $invitation = TeamInvitation::query()->create([
        'invited_by_user_id' => $lead->id,
        'email' => 'joiner@example.com',
        'role' => Role::Member->value,
        'token' => 'invite-token-weak-password',
        'expires_at' => now()->addDays(7),
    ]);

    $this->from(route('invites.accept', $invitation))
        ->post(route('invites.accept.store', $invitation), [
            'name' => 'Joiner',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('invites.accept', $invitation))
        ->assertSessionHasErrors('password');

    $this->assertGuest();
    expect(User::query()->where('email', 'joiner@example.com')->exists())->toBeFalse();
});
