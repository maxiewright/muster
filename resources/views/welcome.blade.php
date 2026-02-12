<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="h-dvh overflow-hidden bg-zinc-950 text-zinc-100 antialiased">
        <div class="relative isolate flex h-dvh flex-col overflow-hidden">
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_20%_20%,rgba(16,185,129,0.2),transparent_30%),radial-gradient(circle_at_80%_0%,rgba(245,158,11,0.16),transparent_32%),linear-gradient(180deg,#09090b_0%,#18181b_50%,#09090b_100%)]"></div>
            <div class="absolute inset-0 -z-10 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:48px_48px]"></div>

            <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-10">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-400/40 bg-emerald-500/15 text-emerald-300">
                        <flux:icon.shield-check class="size-5" />
                    </span>
                    <div>
                        <p class="text-sm uppercase tracking-[0.22em] text-zinc-400">Muster</p>
                        <p class="text-base font-semibold">Mission Productivity</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="inline-flex min-h-[44px] items-center rounded-lg border border-zinc-700 px-4 text-sm font-medium text-zinc-200 transition hover:border-zinc-500 hover:bg-zinc-900" wire:navigate>
                            Log in
                        </a>
                    @endif
                </div>
            </header>

            <main class="mx-auto grid w-full max-w-7xl flex-1 items-center gap-10 px-6 pb-10 lg:grid-cols-2 lg:px-10">
                <section class="space-y-6">
                    <p class="inline-flex items-center rounded-full border border-amber-400/40 bg-amber-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-300">
                        Invite-only operations
                    </p>

                    <h1 class="text-4xl font-semibold leading-tight text-zinc-50 sm:text-5xl lg:text-6xl">
                        Build elite focus with
                        <span class="text-emerald-300">daily mission momentum</span>
                    </h1>

                    <p class="max-w-xl text-base text-zinc-300 sm:text-lg">
                        Muster unifies standups, tasks, training goals, and progress streaks so your unit can execute faster and celebrate wins with clear signal.
                    </p>

                    <div class="flex flex-wrap gap-3">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="inline-flex min-h-[44px] items-center rounded-lg border border-zinc-700 px-5 py-2.5 text-sm font-semibold text-zinc-200 transition hover:border-zinc-500 hover:bg-zinc-900" wire:navigate>
                                Continue operations
                            </a>
                        @endif
                    </div>
                </section>

                <section class="grid gap-4">
                    <article class="rounded-2xl border border-zinc-700/80 bg-zinc-900/80 p-5 backdrop-blur">
                        <p class="text-xs uppercase tracking-[0.14em] text-zinc-400">Ops feed</p>
                        <h2 class="mt-2 text-xl font-semibold text-zinc-50">Live squad updates</h2>
                        <p class="mt-2 text-sm text-zinc-300">See daily check-ins, blockers, and readiness in one command center.</p>
                    </article>

                    <article class="rounded-2xl border border-zinc-700/80 bg-zinc-900/80 p-5 backdrop-blur">
                        <p class="text-xs uppercase tracking-[0.14em] text-zinc-400">Progress engine</p>
                        <h2 class="mt-2 text-xl font-semibold text-zinc-50">Streaks, points, and badges</h2>
                        <p class="mt-2 text-sm text-zinc-300">Turn consistent execution into a visible score that drives accountability.</p>
                    </article>

                    <article class="rounded-2xl border border-zinc-700/80 bg-zinc-900/80 p-5 backdrop-blur">
                        <p class="text-xs uppercase tracking-[0.14em] text-zinc-400">Training doctrine</p>
                        <h2 class="mt-2 text-xl font-semibold text-zinc-50">Partner coaching workflows</h2>
                        <p class="mt-2 text-sm text-zinc-300">Request feedback, verify milestones, and keep development goals in formation.</p>
                    </article>
                </section>
            </main>
        </div>
    </body>
</html>
