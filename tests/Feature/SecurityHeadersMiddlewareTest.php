<?php

test('content security policy header is present when enabled', function (): void {
    config()->set('security.csp_enabled', true);
    config()->set('security.csp_reverb_host', 'reverb.test');

    $response = $this->get(route('home'));

    $response->assertHeader('Content-Security-Policy');

    $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');

    expect($contentSecurityPolicy)->toContain("default-src 'self'");
    expect($contentSecurityPolicy)->toContain("script-src 'self' 'nonce-");
    expect($contentSecurityPolicy)->toContain("style-src 'self' 'unsafe-inline'");
    expect($contentSecurityPolicy)->toContain("connect-src 'self' ws://reverb.test wss://reverb.test");
    expect($contentSecurityPolicy)->toContain("img-src 'self' data: blob:");
    expect($contentSecurityPolicy)->toContain("font-src 'self'");
});
