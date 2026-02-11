<?php

declare(strict_types=1);

use App\Models\Badge;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

use App\Models\User;
use Database\Seeders\BadgeSeeder;

test('guests are redirected to the login page', function (): void {
    $response = $this->get(route('gamification'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the achievements page', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('gamification'));
    $response->assertOk();
});

test('achievements page displays points and streak', function (): void {
    $user = User::factory()->create([
        'points' => 150,
        'current_streak' => 5,
        'longest_streak' => 10,
    ]);
    $this->actingAs($user);

    $response = $this->get(route('gamification'));

    $response->assertOk();
    $response->assertSee('150');
    $response->assertSee('5');
    $response->assertSee('10');
});

test('achievements page displays earned badges', function (): void {
    $this->seed(BadgeSeeder::class);
    $user = User::factory()->create();
    $badge = Badge::where('slug', 'first-muster')->first();
    $user->badges()->attach($badge->id, ['earned_at' => now()]);

    $this->actingAs($user);

    $response = $this->get(route('gamification'));

    $response->assertOk();
    $response->assertSee($badge->name);
    $response->assertSee($badge->icon);
});

test('achievements page displays leaderboard', function (): void {
    $user = User::factory()->create(['points' => 100]);
    User::factory()->create(['points' => 500, 'name' => 'Top Scorer']);

    $this->actingAs($user);

    $response = $this->get(route('gamification'));

    $response->assertOk();
    $response->assertSee('Leaderboard');
    $response->assertSee('Top Scorer');
});
