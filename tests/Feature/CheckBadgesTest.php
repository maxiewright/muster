<?php

use App\Enums\Role;
use App\Models\User;
use App\Services\GamificationService;
use Database\Seeders\BadgeSeeder;

it('awards streak badges based on current streak', function () {
    // Seed badges
    $this->seed(BadgeSeeder::class);

    /** @var User $user */
    $user = User::factory()->create([
        'role' => Role::Member,
        'current_streak' => 21,
        'longest_streak' => 21,
        'points' => 0,
    ]);

    $service = app(GamificationService::class);

    $earned = $service->checkBadges($user);

    // Refresh relations
    $user->load('badges');

    $expectedStreakSlugs = [
        'streak-3', 'streak-7', 'streak-14', 'streak-21',
    ];

    foreach ($expectedStreakSlugs as $slug) {
        expect($user->badges->pluck('slug'))
            ->toContain($slug);
    }

    // Should not error on re-run (idempotent)
    $earnedAgain = $service->checkBadges($user);
    expect($earnedAgain)->toBeArray();
});

it('awards points milestone badges based on total points', function () {
    // Seed badges
    $this->seed(BadgeSeeder::class);

    /** @var User $user */
    $user = User::factory()->create([
        'role' => Role::Member,
        'points' => 2500,
        'current_streak' => 0,
        'longest_streak' => 0,
    ]);

    $service = app(GamificationService::class);

    $service->checkBadges($user);
    $user->load('badges');

    $expectedPointSlugs = [
        'points-100', 'points-250', 'points-500', 'points-1000', 'points-2500',
    ];

    foreach ($expectedPointSlugs as $slug) {
        expect($user->badges->pluck('slug'))
            ->toContain($slug);
    }
});
