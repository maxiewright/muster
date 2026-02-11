<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $cspEnabled = (bool) config('security.csp_enabled', true);

        if ($cspEnabled) {
            Vite::useCspNonce();
        }

        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($cspEnabled) {
            $response->headers->set('Content-Security-Policy', $this->buildContentSecurityPolicy());
        }

        return $response;
    }

    private function buildContentSecurityPolicy(): string
    {
        $nonce = Vite::cspNonce();

        $scriptSources = ["'self'"];

        if (is_string($nonce) && $nonce !== '') {
            $scriptSources[] = "'nonce-{$nonce}'";
        }

        $connectSources = array_merge(["'self'"], $this->reverbConnectSources());

        return implode('; ', [
            "default-src 'self'",
            'script-src '.implode(' ', array_unique($scriptSources)),
            "style-src 'self' 'unsafe-inline'",
            'connect-src '.implode(' ', array_unique($connectSources)),
            "img-src 'self' data: blob:",
            "font-src 'self'",
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function reverbConnectSources(): array
    {
        $reverbHost = (string) config('security.csp_reverb_host', '');

        if ($reverbHost === '') {
            return [];
        }

        $urlParts = parse_url($reverbHost);
        $host = $urlParts['host'] ?? $reverbHost;
        $port = $urlParts['port'] ?? null;

        $hostWithPort = is_int($port) ? "{$host}:{$port}" : $host;

        return [
            "ws://{$hostWithPort}",
            "wss://{$hostWithPort}",
        ];
    }
}
