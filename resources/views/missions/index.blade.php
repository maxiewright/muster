<x-layouts::app>
    <div class="space-y-6">
        <flux:card class="relative overflow-hidden border-emerald-200/70 bg-gradient-to-br from-emerald-50 via-white to-slate-50 text-slate-900 dark:border-zinc-700 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-800 dark:text-zinc-100">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.28),transparent_45%)]"></div>
            <div class="relative">
                <flux:heading level="1" size="xl" class="!text-slate-900 dark:!text-zinc-50">Missions</flux:heading>
                <flux:text class="mt-2 !text-slate-600 dark:!text-zinc-300">
                    Plan missions for {{ $activeUnit->name }} and establish the command roster before actions are assigned.
                </flux:text>
            </div>
        </flux:card>

        @if($canManageMissions)
            <flux:card>
                @if(session('status'))
                    <div class="mb-4 rounded-lg border border-emerald-300/40 bg-emerald-500/10 p-3 text-sm text-emerald-700 dark:text-emerald-300">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('missions.store') }}" class="space-y-4">
                    @csrf

                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:field>
                            <flux:label>Mission Name</flux:label>
                            <flux:input name="name" :value="old('name')" required placeholder="Harbor Clearance" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Mission Commander</flux:label>
                            <flux:select name="mission_commander_user_id" required>
                                @foreach($unitMembers as $member)
                                    <flux:select.option value="{{ $member->id }}" :selected="(int) old('mission_commander_user_id', auth()->id()) === $member->id">
                                        {{ $member->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="mission_commander_user_id" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea name="description" rows="3" placeholder="Mission intent, objectives, and commander notes.">{{ old('description') }}</flux:textarea>
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Permanent Roster</flux:label>
                        <div class="mt-2 grid gap-2 md:grid-cols-2">
                            @foreach($unitMembers as $member)
                                <label class="flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                                    <flux:checkbox name="roster_user_ids[]" value="{{ $member->id }}" :checked="in_array($member->id, old('roster_user_ids', [auth()->id()]), true)" />
                                    <span>{{ $member->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <flux:error name="roster_user_ids" />
                    </flux:field>

                    <flux:button type="submit" variant="primary" class="min-h-[44px] !bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600">
                        Create Mission
                    </flux:button>
                </form>
            </flux:card>
        @endif

        <flux:card class="p-0 overflow-hidden">
            <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900/50">
                <flux:heading level="2">Active Unit Missions</flux:heading>
            </div>

            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($missions as $mission)
                    <div class="p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <flux:heading level="3">{{ $mission->name }}</flux:heading>
                                @if($mission->description)
                                    <flux:text size="sm" class="mt-1">{{ $mission->description }}</flux:text>
                                @endif
                                <flux:text size="sm" class="mt-2">
                                    Commander: {{ $mission->commander?->name ?? 'Unassigned' }} ·
                                    {{ $mission->current_memberships_count }} roster member{{ $mission->current_memberships_count === 1 ? '' : 's' }} ·
                                    {{ $mission->actions_count }} action{{ $mission->actions_count === 1 ? '' : 's' }}
                                </flux:text>
                            </div>

                            <flux:badge color="emerald">Mission</flux:badge>
                        </div>
                    </div>
                @empty
                    <div class="p-4">
                        <flux:text>No missions have been created for this unit yet.</flux:text>
                    </div>
                @endforelse
            </div>
        </flux:card>
    </div>
</x-layouts::app>
