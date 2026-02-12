<?php

use App\Models\User;

test('guest users can view the home page', function (): void {
    User::factory()->lead()->create();

    $response = $this->get(route('home'));

    $response->assertOk();
});

test('home redirects to setup when no users exist', function (): void {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('setup'));
});

test('authenticated users are redirected from home to dashboard', function (): void {
    $user = User::factory()->lead()->create();
    $this->actingAs($user);

    $response = $this->get(route('home'));

    $response->assertRedirect(route('dashboard'));
});
