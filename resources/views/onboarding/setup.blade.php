<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="dark min-h-dvh bg-zinc-950 text-zinc-100 antialiased">
        <div class="relative isolate flex min-h-dvh items-center justify-center overflow-hidden px-6 py-10">
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_15%_20%,rgba(16,185,129,0.28),transparent_30%),radial-gradient(circle_at_80%_0%,rgba(245,158,11,0.2),transparent_35%),linear-gradient(180deg,#09090b_0%,#18181b_50%,#09090b_100%)]"></div>
            <div class="absolute inset-0 -z-10 bg-[linear-gradient(rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.04)_1px,transparent_1px)] bg-[size:44px_44px]"></div>

            <div class="w-full max-w-md rounded-2xl border border-zinc-600/90 bg-zinc-900/95 p-6 shadow-2xl backdrop-blur">
                <flux:heading level="1" size="xl" class="!text-zinc-50">Initialize Command</flux:heading>
                <flux:text class="mt-2 !text-zinc-200">Create the first lead account to start inviting your team.</flux:text>

                <form method="POST" action="{{ route('setup.store') }}" class="mt-6 space-y-4">
                    @csrf

                    <flux:field>
                        <flux:label class="!text-zinc-200">Commander Name</flux:label>
                        <flux:input name="name" :value="old('name')" required autofocus />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="!text-zinc-200">Email</flux:label>
                        <flux:input name="email" type="email" :value="old('email')" required autocomplete="email" />
                        <flux:error name="email" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="!text-zinc-200">Password</flux:label>
                        <flux:input name="password" type="password" required autocomplete="new-password" viewable />
                        <flux:error name="password" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="!text-zinc-200">Confirm Password</flux:label>
                        <flux:input name="password_confirmation" type="password" required autocomplete="new-password" viewable />
                    </flux:field>

                    <flux:button type="submit" variant="primary" class="w-full min-h-[44px] !bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600">
                        Create Lead Account
                    </flux:button>
                </form>
            </div>
        </div>
    </body>
</html>
