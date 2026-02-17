<?php

use Illuminate\Support\Facades\Route;

test('404 page uses the branded error layout', function (): void {
    $response = $this->get('/missing-page-for-error-layout-test');

    $response
        ->assertStatus(404)
        ->assertSee('Error 404')
        ->assertSee('Back to home');
});

test('500 page uses the branded error layout', function (): void {
    config(['app.debug' => false]);

    Route::get('/__test-server-error', fn () => abort(500));

    $response = $this->get('/__test-server-error');

    $response
        ->assertStatus(500)
        ->assertSee('Error 500')
        ->assertSee('Back to home');
});

test('503 page uses the branded error layout', function (): void {
    config(['app.debug' => false]);

    Route::get('/__test-maintenance-error', fn () => abort(503));

    $response = $this->get('/__test-maintenance-error');

    $response
        ->assertStatus(503)
        ->assertSee('Error 503')
        ->assertSee('Back to home');
});
