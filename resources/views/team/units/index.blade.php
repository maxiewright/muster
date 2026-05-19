<x-layouts::app>
    <div class="space-y-6">
        <flux:card class="relative overflow-hidden border-emerald-200/70 bg-gradient-to-br from-emerald-50 via-white to-slate-50 text-slate-900 dark:border-zinc-700 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-800 dark:text-zinc-100">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.28),transparent_45%)]"></div>
            <div class="relative">
                <flux:heading level="1" size="xl" class="!text-slate-900 dark:!text-zinc-50">Units</flux:heading>
                <flux:text class="mt-2 !text-slate-600 dark:!text-zinc-300">
                    Create and manage units inside {{ $organization?->name ?? 'your organization' }}.
                </flux:text>
            </div>
        </flux:card>

        <flux:card>
            @if(session('status'))
                <div class="mb-4 rounded-lg border border-emerald-300/40 bg-emerald-500/10 p-3 text-sm text-emerald-700 dark:text-emerald-300">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('team.units.store') }}" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                @csrf

                <flux:field>
                    <flux:label>Unit Name</flux:label>
                    <flux:input name="name" :value="old('name')" required placeholder="Bravo Unit" />
                    <flux:error name="name" />
                </flux:field>

                <div>
                    <flux:button type="submit" variant="primary" class="min-h-[44px] !bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600">
                        Create Unit
                    </flux:button>
                </div>
            </form>
        </flux:card>

        <flux:card class="p-0 overflow-hidden">
            <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900/50">
                <flux:heading level="2">Organization Units</flux:heading>
            </div>

            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($units as $unit)
                    <div class="flex items-center justify-between gap-4 p-4">
                        <div>
                            <flux:heading level="3">{{ $unit->name }}</flux:heading>
                            <flux:text size="sm">{{ $unit->users_count }} assigned member{{ $unit->users_count === 1 ? '' : 's' }}</flux:text>
                        </div>

                        @if($activeUnit?->id === $unit->id)
                            <flux:badge color="emerald">Active Unit</flux:badge>
                        @endif
                    </div>
                @empty
                    <div class="p-4">
                        <flux:text>No units have been created yet.</flux:text>
                    </div>
                @endforelse
            </div>
        </flux:card>
    </div>
</x-layouts::app>
