<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
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

        if (! app()->isLocal()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if ($cspEnabled) {
            $response->headers->set('Content-Security-Policy', $this->buildContentSecurityPolicy());
        }

        return $response;
    }

    private function buildContentSecurityPolicy(): string
    {
        $nonce = Vite::cspNonce();
        $viteHotSources = $this->viteHotSources();
        $fontSources = array_merge(["'self'", 'https://fonts.bunny.net', 'https://fonts.gstatic.com'], $viteHotSources['origins']);
        $styleSources = array_merge(["'self'", "'unsafe-inline'", 'https://fonts.bunny.net'], $viteHotSources['origins']);

        $scriptSources = array_merge(["'self'"], $viteHotSources['origins']);
        $scriptHashes = [
            "'sha256-abS8bXelr2wTMtWfwv4Q2SgF9jc3EmpFalJLyucKH4o='",
            "'sha256-DBHp8ZBqUgtHo41U8edYcL1Hi9oa4qRKdLcdX5BB5XE='",
            "'sha256-joq/V7C+31bi/GIzBBTHlR2WvDb796LEG+i7tbRXnJo='",
        ];
        $connectSources = array_merge(["'self'"], $this->reverbConnectSources(), $viteHotSources['connect']);
        $imageSources = array_merge(["'self'", 'data:', 'blob:', 'https://www.gravatar.com'], $viteHotSources['origins']);

        if (is_string($nonce) && $nonce !== '') {
            $scriptSources[] = "'nonce-{$nonce}'";
        }

        $scriptSources = array_merge($scriptSources, $scriptHashes);

        if ((bool) config('app.debug', false)) {
            $scriptSources[] = "'unsafe-eval'";
        }

        return implode('; ', [
            "default-src 'self'",
            'script-src '.implode(' ', array_unique($scriptSources)),
            'style-src '.implode(' ', array_unique($styleSources)),
            'connect-src '.implode(' ', array_unique($connectSources)),
            'img-src '.implode(' ', array_unique($imageSources)),
            'font-src '.implode(' ', array_unique($fontSources)),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function reverbConnectSources(): array
    {
        $reverbHost = (string) config('security.csp_reverb_host', '');
        $reverbPort = config('security.csp_reverb_port');

        if ($reverbHost === '') {
            return [];
        }

        $hostValue = Str::contains($reverbHost, '://') ? $reverbHost : "//{$reverbHost}";
        $urlParts = parse_url($hostValue);
        $host = $urlParts['host'] ?? $urlParts['path'] ?? $reverbHost;
        $port = $urlParts['port'] ?? null;

        if (! is_int($port) && is_numeric($reverbPort)) {
            $port = (int) $reverbPort;
        }

        $hostWithPort = is_int($port) ? "{$host}:{$port}" : $host;

        return [
            "ws://{$hostWithPort}",
            "wss://{$hostWithPort}",
        ];
    }

    /**
     * @return array{origins: array<int, string>, connect: array<int, string>}
     */
    private function viteHotSources(): array
    {
        if (! Vite::isRunningHot()) {
            return ['origins' => [], 'connect' => []];
        }

        $hotFile = Vite::hotFile();

        if (! is_file($hotFile)) {
            return ['origins' => [], 'connect' => []];
        }

        $hotUrl = trim((string) file_get_contents($hotFile));

        if ($hotUrl === '') {
            return ['origins' => [], 'connect' => []];
        }

        $urlParts = parse_url($hotUrl);
        $scheme = $urlParts['scheme'] ?? 'http';
        $host = $urlParts['host'] ?? null;
        $port = $urlParts['port'] ?? null;

        if (! is_string($host) || $host === '') {
            return ['origins' => [], 'connect' => []];
        }

        $hostWithPort = is_int($port) ? "{$host}:{$port}" : $host;
        $origin = "{$scheme}://{$hostWithPort}";

        $socketScheme = Str::startsWith($scheme, 'https') ? 'wss' : 'ws';

        return [
            'origins' => [$origin],
            'connect' => [
                $origin,
                "{$socketScheme}://{$hostWithPort}",
            ],
        ];
    }
}
