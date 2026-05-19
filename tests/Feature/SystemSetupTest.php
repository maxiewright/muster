<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\User;

test('system setup page is available before the platform admin exists', function (): void {
    $this->get(route('system.setup'))
        ->assertOk()
        ->assertSee('Initialize Muster');
});

test('system setup creates the first platform admin account', function (): void {
    $this->post(route('system.setup.store'), [
        'name' => 'Muster Operator',
        'email' => 'ops@example.com',
        'password' => 'AdminPass123!',
        'password_confirmation' => 'AdminPass123!',
    ])->assertRedirect(route('filament.admin.pages.dashboard'));

    $user = User::query()->where('email', 'ops@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user?->is_platform_admin)->toBeTrue();
    expect($user?->role)->toBe(Role::Member);
    $this->assertAuthenticatedAs($user);
});

test('system setup is unavailable once a platform admin exists', function (): void {
    User::factory()->create(['is_platform_admin' => true]);

    $this->get(route('system.setup'))
        ->assertRedirect(route('login'));
});
