<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
<meta name="theme-color" content="rgb(39 39 42)" media="(prefers-color-scheme: dark)" />
<meta name="theme-color" content="rgb(255 255 255)" media="(prefers-color-scheme: light)" />

<title>{{ $title ?? config('app.name') }}</title>

@php
    $faviconIcoVersion = filemtime(public_path('favicon.ico'));
    $faviconSvgVersion = filemtime(public_path('favicon.svg'));
    $favicon16Version = filemtime(public_path('favicon-16x16.png'));
    $favicon32Version = filemtime(public_path('favicon-32x32.png'));
    $appleTouchIconVersion = filemtime(public_path('apple-touch-icon.png'));
    $manifestVersion = filemtime(public_path('site.webmanifest'));
    $openGraphImageVersion = filemtime(public_path('og-image.png'));
@endphp
<link rel="icon" href="{{ asset('favicon.ico').'?v='.$faviconIcoVersion }}" sizes="any">
<link rel="shortcut icon" href="{{ asset('favicon.ico').'?v='.$faviconIcoVersion }}" type="image/x-icon">
<link rel="icon" href="{{ asset('favicon.svg').'?v='.$faviconSvgVersion }}" type="image/svg+xml">
<link rel="icon" href="{{ asset('favicon-16x16.png').'?v='.$favicon16Version }}" sizes="16x16" type="image/png">
<link rel="icon" href="{{ asset('favicon-32x32.png').'?v='.$favicon32Version }}" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png').'?v='.$appleTouchIconVersion }}" sizes="180x180">
<link rel="manifest" href="{{ asset('site.webmanifest').'?v='.$manifestVersion }}">
<meta property="og:image" content="{{ asset('og-image.png').'?v='.$openGraphImageVersion }}">

{{-- Instrument Sans is self-hosted via @fontsource (bundled into app.css), so no preconnect /
     blocking stylesheet from fonts.bunny.net is needed. --}}

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance(['nonce' => \Illuminate\Support\Facades\Vite::cspNonce()])
