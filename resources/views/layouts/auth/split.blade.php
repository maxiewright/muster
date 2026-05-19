<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="dark min-h-dvh bg-slate-50 text-slate-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <div class="relative grid min-h-dvh lg:grid-cols-2">
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_15%_20%,rgba(16,185,129,0.10),transparent_30%),radial-gradient(circle_at_80%_0%,rgba(56,189,248,0.08),transparent_33%),linear-gradient(180deg,#f8fafc_0%,#f1f5f9_50%,#f8fafc_100%)] dark:bg-[radial-gradient(circle_at_15%_20%,rgba(16,185,129,0.22),transparent_30%),radial-gradient(circle_at_80%_0%,rgba(245,158,11,0.18),transparent_33%),linear-gradient(180deg,#09090b_0%,#18181b_50%,#09090b_100%)]"></div>
            <div class="absolute inset-0 -z-10 bg-[linear-gradient(rgba(148,163,184,0.10)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.10)_1px,transparent_1px)] bg-[size:44px_44px] dark:bg-[linear-gradient(rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.04)_1px,transparent_1px)]"></div>

            <aside class="hidden border-r border-slate-200 p-10 dark:border-zinc-800/70 lg:flex lg:flex-col">
                <a href="{{ route('home') }}" class="relative z-20 flex items-center gap-3 text-lg font-medium text-slate-800 dark:text-zinc-100" wire:navigate>
                    <span class="inline-flex h-11 w-11 items-center justify-center">
                        <x-app-logo-icon />
                    </span>
                    {{ config('app.name', 'Muster') }}
                </a>

                <div class="relative z-20 mt-auto max-w-md space-y-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-zinc-300">Mission Access</p>
                    <flux:heading size="xl" class="!text-slate-900 dark:!text-zinc-50">Invite-only operations for high-accountability teams.</flux:heading>
                    <flux:text class="!text-slate-600 dark:!text-zinc-200">Coordinate musters, training, and execution from one secure command center.</flux:text>
                </div>
            </aside>

            <main class="flex items-center justify-center px-6 py-10 lg:px-12">
                <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white/95 p-6 shadow-xl backdrop-blur dark:border-zinc-600/90 dark:bg-zinc-900/95 dark:shadow-2xl">
                    <a href="{{ route('home') }}" class="mb-6 flex items-center justify-center gap-2 text-slate-800 dark:text-zinc-100 lg:hidden" wire:navigate>
                        <span class="inline-flex h-8 w-8 items-center justify-center">
                            <x-app-logo-icon />
                        </span>
                        <span class="font-semibold">{{ config('app.name', 'Muster') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </main>
        </div>

        @fluxScripts(['nonce' => \Illuminate\Support\Facades\Vite::cspNonce()])
    </body>
</html>
