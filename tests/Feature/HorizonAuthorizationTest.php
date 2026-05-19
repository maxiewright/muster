<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('allows leads to view horizon', function (): void {
    $lead = User::factory()->lead()->create();

    expect(Gate::forUser($lead)->allows('viewHorizon'))->toBeTrue();
});

it('denies members from viewing horizon', function (): void {
    $member = User::factory()->create();

    expect(Gate::forUser($member)->allows('viewHorizon'))->toBeFalse();
});
