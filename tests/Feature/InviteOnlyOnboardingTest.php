<?php

use App\Enums\Role;
use App\Mail\TeamInvitationMail;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;

test('first-time visitors are redirected to setup when no users exist', function (): void {
    $this->get(route('home'))
        ->assertRedirect(route('setup'));
});

test('visitors are redirected to setup when no lead user exists', function (): void {
    User::factory()->create();

    $this->get(route('home'))
        ->assertRedirect(route('setup'));
});

test('first setup can create the initial lead account', function (): void {
    $this->post(route('setup.store'), [
        'name' => 'Initial Commander',
        'email' => 'commander@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('dashboard'));

    $user = User::query()->first();

    expect($user)->not->toBeNull();
    expect($user?->role)->toBe(Role::Lead);
    $this->assertAuthenticatedAs($user);
});

test('lead can send an email invitation', function (): void {
    Mail::fake();

    $lead = User::factory()->lead()->create();

    actingAs($lead)
        ->post(route('team.invitations.store'), [
            'email' => 'new.member@example.com',
            'role' => Role::Member->value,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $invitation = TeamInvitation::query()->first();

    expect($invitation)->not->toBeNull();
    expect($invitation?->email)->toBe('new.member@example.com');

    Mail::assertSent(TeamInvitationMail::class, 1);
});

test('invited user can accept invitation and join team', function (): void {
    $lead = User::factory()->lead()->create();

    $invitation = TeamInvitation::query()->create([
        'invited_by_user_id' => $lead->id,
        'email' => 'joiner@example.com',
        'role' => Role::Member->value,
        'token' => 'invite-token-123',
        'expires_at' => now()->addDays(7),
    ]);

    $this->post(route('invites.accept.store', $invitation), [
        'name' => 'Joiner',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('dashboard'));

    $user = User::query()->where('email', 'joiner@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user?->role)->toBe(Role::Member);

    $invitation->refresh();
    expect($invitation->accepted_at)->not->toBeNull();
    $this->assertAuthenticatedAs($user);
});
