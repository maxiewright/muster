<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

test('primary muster routes use canonical muster naming', function (): void {
    $createRoute = Route::getRoutes()->getByName('muster.create');
    $editRoute = Route::getRoutes()->getByName('muster.edit');

    expect($createRoute)->not->toBeNull()
        ->and($editRoute)->not->toBeNull()
        ->and($createRoute?->gatherMiddleware())->toContain('throttle:muster-submit')
        ->and($editRoute?->uri())->toBe('musters/{muster}/edit');
});
