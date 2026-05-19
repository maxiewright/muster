<?php

declare(strict_types=1);

use App\Enums\ProgressTier;

test('fromPoints resolves correct progress tier at each threshold', function (int $points, ProgressTier $expectedTier): void {
    expect(ProgressTier::fromPoints($points))->toBe($expectedTier);
})->with([
    'zero points' => [0, ProgressTier::Foundation],
    'just below Building' => [49, ProgressTier::Foundation],
    'exactly Building' => [50, ProgressTier::Building],
    'mid Developing' => [200, ProgressTier::Developing],
    'exactly Capable' => [500, ProgressTier::Capable],
    'high Advanced' => [1500, ProgressTier::Advanced],
    'exactly Vanguard' => [5000, ProgressTier::Vanguard],
    'above Vanguard' => [9999, ProgressTier::Vanguard],
]);

test('nextRank returns correct next progress tier', function (): void {
    expect(ProgressTier::Foundation->nextTier())->toBe(ProgressTier::Building);
    expect(ProgressTier::Capable->nextTier())->toBe(ProgressTier::Proficient);
});

test('nextRank returns null for vanguard', function (): void {
    expect(ProgressTier::Vanguard->nextTier())->toBeNull();
});

test('progressToNext returns correct percentage', function (): void {
    expect(ProgressTier::Foundation->progressToNext(25))->toBe(50);

    expect(ProgressTier::Foundation->progressToNext(0))->toBe(0);
});

test('progressToNext returns 100 for the top tier', function (): void {
    expect(ProgressTier::Vanguard->progressToNext(5000))->toBe(100);
    expect(ProgressTier::Vanguard->progressToNext(9999))->toBe(100);
});

test('every tier has a label, icon, and minPoints', function (): void {
    foreach (ProgressTier::cases() as $tier) {
        expect($tier->label())->toBeString()->not->toBeEmpty();
        expect($tier->icon())->toBeString()->not->toBeEmpty();
        expect($tier->minPoints())->toBeInt()->toBeGreaterThanOrEqual(0);
    }
});

test('tiers are ordered by ascending minPoints', function (): void {
    $cases = ProgressTier::cases();
    $counter = count($cases);
    for ($i = 1; $i < $counter; $i++) {
        expect($cases[$i]->minPoints())->toBeGreaterThan($cases[$i - 1]->minPoints());
    }
});
