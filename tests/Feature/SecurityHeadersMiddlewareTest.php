<?php

use App\Models\User;
use Illuminate\Support\Facades\Vite;

test('content security policy header is present when enabled', function (): void {
    config()->set('security.csp_enabled', true);
    config()->set('security.csp_reverb_host', 'reverb.test');
    config()->set('security.csp_reverb_port', null);

    $response = $this->get(route('home'));

    $response->assertHeader('Content-Security-Policy');

    $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');

    expect($contentSecurityPolicy)->toContain("default-src 'self'");
    expect($contentSecurityPolicy)->toContain("script-src 'self'");
    expect($contentSecurityPolicy)->toContain("'nonce-");
    expect($contentSecurityPolicy)->toContain("'sha256-abS8bXelr2wTMtWfwv4Q2SgF9jc3EmpFalJLyucKH4o='");
    expect($contentSecurityPolicy)->toContain("'sha256-DBHp8ZBqUgtHo41U8edYcL1Hi9oa4qRKdLcdX5BB5XE='");
    expect($contentSecurityPolicy)->toContain("'sha256-joq/V7C+31bi/GIzBBTHlR2WvDb796LEG+i7tbRXnJo='");
    expect($contentSecurityPolicy)->toContain("'unsafe-eval'");
    expect($contentSecurityPolicy)->toContain("style-src 'self' 'unsafe-inline' https://fonts.bunny.net");
    expect($contentSecurityPolicy)->toContain("connect-src 'self'");
    expect($contentSecurityPolicy)->toContain('ws://reverb.test');
    expect($contentSecurityPolicy)->toContain('wss://reverb.test');
    expect($contentSecurityPolicy)->toContain("img-src 'self' data: blob: https://www.gravatar.com");
    expect($contentSecurityPolicy)->toContain("font-src 'self' https://fonts.bunny.net https://fonts.gstatic.com");
});

test('content security policy includes reverb port in websocket sources', function (): void {
    config()->set('security.csp_enabled', true);
    config()->set('security.csp_reverb_host', 'localhost');
    config()->set('security.csp_reverb_port', 8080);

    $response = $this->get(route('home'));

    $response->assertHeader('Content-Security-Policy');

    $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');

    expect($contentSecurityPolicy)->toContain("connect-src 'self'");
    expect($contentSecurityPolicy)->toContain('ws://localhost:8080');
    expect($contentSecurityPolicy)->toContain('wss://localhost:8080');
});

test('content security policy allows vite hot assets when dev server is running', function (): void {
    config()->set('security.csp_enabled', true);
    config()->set('security.csp_reverb_host', '');

    $hotFile = storage_path('framework/testing/vite.hot');

    if (! is_dir(dirname($hotFile))) {
        mkdir(dirname($hotFile), 0755, true);
    }

    file_put_contents($hotFile, 'https://muster.test:5173');
    Vite::useHotFile($hotFile);

    try {
        $response = $this->get(route('home'));
    } finally {
        @unlink($hotFile);
        Vite::useHotFile(public_path('/hot'));
    }

    $response->assertHeader('Content-Security-Policy');

    $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');

    expect($contentSecurityPolicy)->toContain("script-src 'self' https://muster.test:5173 'nonce-");
    expect($contentSecurityPolicy)->toContain("'unsafe-eval'");
    expect($contentSecurityPolicy)->toContain("style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://muster.test:5173");
    expect($contentSecurityPolicy)->toContain("connect-src 'self' https://muster.test:5173 wss://muster.test:5173");
    expect($contentSecurityPolicy)->toContain("img-src 'self' data: blob: https://www.gravatar.com https://muster.test:5173");
    expect($contentSecurityPolicy)->toContain("font-src 'self' https://fonts.bunny.net https://fonts.gstatic.com https://muster.test:5173");
});

test('flux assets render with csp nonce attributes', function (): void {
    config()->set('security.csp_enabled', true);
    User::factory()->lead()->create();

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('nonce=', false);
});

test('training page inline scripts include a csp nonce', function (): void {
    config()->set('security.csp_enabled', true);

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('training.dashboard'));
    $response->assertOk();

    preg_match_all('/<script(?![^>]*\bsrc=)(?![^>]*\bnonce=)[^>]*>/i', $response->getContent(), $matches);

    expect($matches[0])->toBeArray()->toHaveCount(0);
});
