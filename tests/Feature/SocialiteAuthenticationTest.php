<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Organization;
use App\Models\TeamInvitation;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

it('stores oauth state in the session during socialite redirects', function (): void {
    config()->set('services.github.client_id', 'github-client-id');
    config()->set('services.github.client_secret', 'github-client-secret');
    config()->set('services.github.redirect', route('socialite.callback', ['provider' => 'github']));

    $response = $this->get(route('socialite.redirect', ['provider' => 'github']));

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('state=');

    expect(session()->has('state'))->toBeTrue();
    expect(session('state'))->not()->toBeEmpty();
});

it('accepts invited socialite users into the invitation organization and unit', function (): void {
    $organization = Organization::factory()->create();
    $alphaUnit = Unit::factory()->for($organization)->create(['name' => 'Alpha Unit']);
    $bravoUnit = Unit::factory()->for($organization)->create(['name' => 'Bravo Unit']);
    $lead = User::factory()->lead()->create(['organization_id' => $organization->id]);

    UnitMembership::factory()->create([
        'user_id' => $lead->id,
        'unit_id' => $alphaUnit->id,
    ]);

    UnitMembership::factory()->create([
        'user_id' => $lead->id,
        'unit_id' => $bravoUnit->id,
    ]);

    $invitation = TeamInvitation::query()->create([
        'invited_by_user_id' => $lead->id,
        'organization_id' => $organization->id,
        'unit_id' => $bravoUnit->id,
        'email' => 'oauth.joiner@example.com',
        'role' => Role::Member->value,
        'token' => 'socialite-invite-token',
        'expires_at' => now()->addDays(7),
    ]);

    $socialiteUser = new SocialiteUser;
    $socialiteUser->map([
        'id' => 'provider-user-123',
        'nickname' => 'oauth-joiner',
        'name' => 'OAuth Joiner',
        'email' => 'oauth.joiner@example.com',
    ]);

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->once()
        ->with('github')
        ->andReturn($provider);

    $this->get(route('socialite.callback', ['provider' => 'github']))
        ->assertRedirect(route('dashboard'));

    $user = User::query()->where('email', 'oauth.joiner@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user?->organization_id)->toBe($organization->id);
    expect(UnitMembership::query()->where('user_id', $user?->id)->where('unit_id', $bravoUnit->id)->exists())->toBeTrue();
    expect(UnitMembership::query()->where('user_id', $user?->id)->where('unit_id', $alphaUnit->id)->exists())->toBeFalse();
    expect($invitation->fresh()->accepted_at)->not->toBeNull();
});
