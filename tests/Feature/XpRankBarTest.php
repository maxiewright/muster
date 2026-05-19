<?php

declare(strict_types=1);

use App\Livewire\XpRankBar;
use App\Models\User;
use Livewire\Livewire;

test('xp progress bar mounts with correct tier for user', function (): void {
    $user = User::factory()->create(['points' => 500]);

    Livewire::actingAs($user)
        ->test(XpRankBar::class)
        ->assertSet('rankLabel', 'Capable')
        ->assertSet('points', 500)
        ->assertSee('Capable')
        ->assertSee('500 XP');
});

test('xp progress bar shows foundation for zero points', function (): void {
    $user = User::factory()->create(['points' => 0]);

    Livewire::actingAs($user)
        ->test(XpRankBar::class)
        ->assertSet('rankLabel', 'Foundation')
        ->assertSet('progressPercent', 0);
});

test('xp progress bar shows top tier message for vanguard', function (): void {
    $user = User::factory()->create(['points' => 5000]);

    Livewire::actingAs($user)
        ->test(XpRankBar::class)
        ->assertSet('rankLabel', 'Vanguard')
        ->assertSet('progressPercent', 100)
        ->assertSee('Top tier reached');
});

test('xp rank bar refreshes on points-awarded event', function (): void {
    $user = User::factory()->create(['points' => 45]);

    $component = Livewire::actingAs($user)
        ->test(XpRankBar::class)
        ->assertSet('rankLabel', 'Foundation');

    $user->forceFill(['points' => 55])->save();

    $component->dispatch('points-awarded')
        ->assertSet('rankLabel', 'Building')
        ->assertSet('points', 55);
});

test('xp progress bar displays progress to the next tier', function (): void {
    $user = User::factory()->create(['points' => 25]);

    Livewire::actingAs($user)
        ->test(XpRankBar::class)
        ->assertSet('nextRankLabel', 'Building')
        ->assertSet('nextRankPoints', 50)
        ->assertSee('25 XP to Building');
});
