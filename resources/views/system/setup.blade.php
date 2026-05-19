<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Initialize Muster')"
            :description="__('Create the first private platform administrator for organization provisioning and reporting.')"
        />

        <form method="POST" action="{{ route('system.setup.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="name"
                :label="__('Administrator Name')"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                placeholder="Platform Administrator"
            />

            <flux:input
                name="email"
                :label="__('Administrator Email')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="admin@example.com"
            />

            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Password"
                viewable
            />

            <flux:input
                name="password_confirmation"
                :label="__('Confirm Password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Confirm Password"
                viewable
            />

            <div class="rounded-lg border border-emerald-300/40 bg-emerald-500/10 p-4 text-sm text-emerald-700 dark:text-emerald-300">
                This account is for the private platform admin area only. Organization commanders will still onboard separately through bootstrap invitations.
            </div>

            <flux:button variant="primary" type="submit" class="w-full min-h-[44px] !bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600">
                Complete System Setup
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
