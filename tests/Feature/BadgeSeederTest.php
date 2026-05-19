<?php

declare(strict_types=1);

use App\Models\Badge;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('badge seeder uses neutral non-military achievement language', function (): void {
    $this->seed(BadgeSeeder::class);

    $legacyTerms = [
        'recruit',
        'private',
        'corporal',
        'sergeant',
        'lieutenant',
        'captain',
        'major',
        'colonel',
        'general',
        'commander',
        'cadet',
        'centurion',
        'warrant officer',
        'squad leader',
        'battle buddy',
        'weekend warrior',
        'reveille',
        'force multiplier',
        'unit cohesion',
        'campaign medal',
        'meritorious service',
        'war hero',
        'battle hardened',
    ];

    $badges = Badge::query()->get(['name', 'description']);

    foreach ($badges as $badge) {
        $searchableText = str($badge->name.' '.$badge->description)->lower()->value();

        foreach ($legacyTerms as $legacyTerm) {
            expect($searchableText)->not->toContain($legacyTerm);
        }
    }
});
