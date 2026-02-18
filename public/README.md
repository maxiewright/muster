# Laravel Project Logo & Favicon Files

## File Reference

| File | Size | Usage |
|------|------|-------|
| `favicon.ico` | Multi-size | Primary browser favicon |
| `favicon-16x16.png` | 16×16 | Small favicon |
| `favicon-32x32.png` | 32×32 | Standard favicon |
| `apple-touch-icon.png` | 180×180 | iOS home screen icon |
| `android-chrome-192x192.png` | 192×192 | Android/PWA icon |
| `android-chrome-512x512.png` | 512×512 | Android/PWA splash |
| `logo-200x200.png` | 200×200 | In-app logo (white bg) |
| `logo-512x512.png` | 512×512 | High-res logo (transparent bg) |
| `og-image.png` | 1200×630 | Open Graph / social sharing |

## Laravel Blade Usage

Place all files in `public/` and add to your `layouts/app.blade.php`:

```html
<head>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- PWA Manifest (optional) -->
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- OG Image -->
    <meta property="og:image" content="{{ asset('og-image.png') }}">
</head>
```

## Filament Panel Usage

In your `AppServiceProvider` or Panel provider:

```php
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        ->brandLogo(asset('logo-200x200.png'))
        ->favicon(asset('favicon.ico'));
}
```
