<?php

declare(strict_types=1);

use App\Models\User;

test('guest users can view the home page', function (): void {
    User::factory()->platformAdmin()->create();

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee(asset('favicon.ico').'?v=', false)
        ->assertSee('src="'.asset('logo.svg').'"', false);
});

test('home redirects to system setup when no platform admin exists', function (): void {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('system.setup'));
});

test('authenticated platform admins are redirected from home to admin dashboard', function (): void {
    $user = User::factory()->platformAdmin()->create();
    $this->actingAs($user);

    $response = $this->get(route('home'));

    $response->assertRedirect(route('filament.admin.pages.dashboard'));
});

test('authenticated regular users are redirected from home to user dashboard', function (): void {
    User::factory()->platformAdmin()->create();
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('home'));

    $response->assertRedirect(route('dashboard'));
});
