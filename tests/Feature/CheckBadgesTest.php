<?php

use App\Enums\Role;
use App\Models\User;
use App\Services\GamificationService;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('awards streak badges based on current streak', function (): void {
    // Seed badges
    $this->seed(BadgeSeeder::class);

    /** @var User $user */
    $user = User::factory()->withStats(points: 0, currentStreak: 21, longestStreak: 21)->create([
        'role' => Role::Member,
    ]);

    $service = app(GamificationService::class);

    $earned = $service->checkBadges($user->fresh());

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

it('awards points milestone badges based on total points', function (): void {
    // Seed badges
    $this->seed(BadgeSeeder::class);

    /** @var User $user */
    $user = User::factory()->withStats(points: 2500, currentStreak: 0, longestStreak: 0)->create([
        'role' => Role::Member,
    ]);

    $service = app(GamificationService::class);

    $service->checkBadges($user->fresh());
    $user->load('badges');

    $expectedPointSlugs = [
        'points-100', 'points-250', 'points-500', 'points-1000', 'points-2500',
    ];

    foreach ($expectedPointSlugs as $slug) {
        expect($user->badges->pluck('slug'))
            ->toContain($slug);
    }
});

it('uses a single batch query to load all badges rather than one query per slug', function (): void {
    $this->seed(BadgeSeeder::class);

    /** @var User $user */
    $user = User::factory()->withStats(points: 100, currentStreak: 7, longestStreak: 7)->create([
        'role' => Role::Member,
    ]);

    $service = app(GamificationService::class);

    $slugLookupCount = 0;

    DB::listen(static function ($query) use (&$slugLookupCount): void {
        // Count queries that look up badges individually by slug — the old N+1 pattern.
        if (str_contains(strtolower($query->sql), '"slug"') || str_contains(strtolower($query->sql), '`slug`')) {
            $slugLookupCount++;
        }
    });

    $service->checkBadges($user);

    // The batched implementation issues at most 1 query that filters by slug
    // (a single whereIn across all slugs). The old N+1 approach would have issued
    // one query per badge slug — up to 15 in total.
    expect($slugLookupCount)->toBeLessThanOrEqual(1);
});
