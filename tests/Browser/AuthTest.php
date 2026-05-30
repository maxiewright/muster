<?php

declare(strict_types=1);

use App\Models\User;

it('allows a user to log in', function () {
    $user = User::factory()->create([
        'email' => 'login_test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $page = visit('/login');

    $page->assertSee('Log in to your account')
        ->fill('email', $user->email)
        ->fill('password', 'password123')
        ->click('Log in')
        ->assertPathIsNot('/login');
});
