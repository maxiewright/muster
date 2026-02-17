<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="dark min-h-dvh bg-zinc-950 text-zinc-100 antialiased">
        <div class="relative grid min-h-dvh lg:grid-cols-2">
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_15%_20%,rgba(16,185,129,0.22),transparent_30%),radial-gradient(circle_at_80%_0%,rgba(245,158,11,0.18),transparent_33%),linear-gradient(180deg,#09090b_0%,#18181b_50%,#09090b_100%)]"></div>
            <div class="absolute inset-0 -z-10 bg-[linear-gradient(rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.04)_1px,transparent_1px)] bg-[size:44px_44px]"></div>

            <aside class="hidden border-r border-zinc-800/70 p-10 lg:flex lg:flex-col">
                <a href="{{ route('home') }}" class="relative z-20 flex items-center gap-3 text-lg font-medium text-zinc-100" wire:navigate>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-400/40 bg-emerald-500/15 text-emerald-300">
                        <x-app-logo-icon class="size-6 fill-current" />
                    </span>
                    {{ config('app.name', 'Muster') }}
                </a>

                <div class="relative z-20 mt-auto max-w-md space-y-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-zinc-300">Mission Access</p>
                    <flux:heading size="xl" class="!text-zinc-50">Invite-only operations for high-accountability teams.</flux:heading>
                    <flux:text class="!text-zinc-200">Coordinate standups, training, and execution from one secure command center.</flux:text>
                </div>
            </aside>

            <main class="flex items-center justify-center px-6 py-10 lg:px-12">
                <div class="w-full max-w-md rounded-2xl border border-zinc-600/90 bg-zinc-900/95 p-6 shadow-2xl backdrop-blur">
                    <a href="{{ route('home') }}" class="mb-6 flex items-center justify-center gap-2 text-zinc-100 lg:hidden" wire:navigate>
                        <x-app-logo-icon class="size-8 fill-current" />
                        <span class="font-semibold">{{ config('app.name', 'Muster') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </main>
        </div>

        @fluxScripts(['nonce' => \Illuminate\Support\Facades\Vite::cspNonce()])
    </body>
</html>
