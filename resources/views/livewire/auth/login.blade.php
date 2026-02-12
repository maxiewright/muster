<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Use your team credentials or an approved social account.')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        @if ($errors->has('socialite'))
            <div class="rounded-lg border border-red-300/40 bg-red-500/10 p-3 text-sm text-red-300">
                {{ $errors->first('socialite') }}
            </div>
        @endif

        @php
            $socialProviders = collect([
                ['name' => 'GitHub', 'route' => route('socialite.redirect', 'github'), 'enabled' => filled(config('services.github.client_id')), 'icon' => 'folder-git-2'],
                ['name' => 'Google', 'route' => route('socialite.redirect', 'google'), 'enabled' => filled(config('services.google.client_id')), 'icon' => 'search'],
            ])->where('enabled', true)->values();
        @endphp

        @if($socialProviders->isNotEmpty())
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach($socialProviders as $provider)
                    <a href="{{ $provider['route'] }}" class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-lg border border-zinc-600 bg-zinc-800 px-3 py-2 text-sm font-medium text-zinc-100 transition hover:border-zinc-500 hover:bg-zinc-700">
                        <flux:icon :icon="$provider['icon']" class="size-4" />
                        Continue with {{ $provider['name'] }}
                    </a>
                @endforeach
            </div>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t border-zinc-700"></span>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-zinc-900 px-2 text-zinc-400">or use email</span>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute end-0 top-0 text-sm !text-zinc-300 hover:!text-zinc-100" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" class="text-zinc-200" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full min-h-[44px] !bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
