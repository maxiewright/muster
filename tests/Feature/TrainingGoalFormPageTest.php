<?php

use App\Models\FocusArea;
use App\Models\User;

it('loads training goal create page for authenticated users', function (): void {
    $user = User::factory()->create();
    FocusArea::factory()->create();

    $this->actingAs($user)
        ->get(route('training.goals.create'))
        ->assertOk()
        ->assertSee('Set a New Training Goal');
});
