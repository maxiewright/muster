<?php

declare(strict_types=1);

use App\Models\User;

test('welcome page uses muster-first language', function (): void {
    User::factory()->platformAdmin()->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Muster unifies musters, tasks, training goals, and progress streaks');
});

test('login page uses muster-first language', function (): void {
    User::factory()->platformAdmin()->create();
    config()->set('services.github.client_id', 'test-client-id');
    config()->set('services.google.client_id', 'test-client-id');

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Coordinate musters, training, and execution from one secure command center.');
});
