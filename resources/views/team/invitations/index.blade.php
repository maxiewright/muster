<x-layouts::app>
    <div class="space-y-6">
        <flux:card class="relative overflow-hidden border-zinc-200/70 bg-gradient-to-br from-zinc-950 via-zinc-900 to-zinc-800 text-zinc-100 dark:border-zinc-700">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.28),transparent_45%)]"></div>
            <div class="relative">
                <flux:heading level="1" size="xl" class="!text-zinc-50">Team Invitations</flux:heading>
                <flux:text class="mt-2 !text-zinc-300">Invite teammates by email. Access is invite-only.</flux:text>
            </div>
        </flux:card>

        <flux:card>
            @if(session('status'))
                <div class="mb-4 rounded-lg border border-emerald-300/40 bg-emerald-500/10 p-3 text-sm text-emerald-700 dark:text-emerald-300">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('team.invitations.store') }}" class="grid gap-4 md:grid-cols-3">
                @csrf

                <flux:field class="md:col-span-2">
                    <flux:label>Email</flux:label>
                    <flux:input name="email" type="email" :value="old('email')" required placeholder="teammate@example.com" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Role</flux:label>
                    <flux:select name="role" required>
                        @foreach($roles as $role)
                            <flux:select.option value="{{ $role->value }}" :selected="old('role', 'member') === $role->value">
                                {{ $role->label() }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="role" />
                </flux:field>

                <div class="md:col-span-3">
                    <flux:button type="submit" variant="primary" class="min-h-[44px] !bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600">
                        Send Invite
                    </flux:button>
                </div>
            </form>
        </flux:card>

        <div class="grid gap-6 lg:grid-cols-2">
            <flux:card class="p-0 overflow-hidden">
                <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900/50">
                    <flux:heading level="2">Pending</flux:heading>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($pendingInvitations as $invitation)
                        <div class="p-4">
                            <flux:heading level="3">{{ $invitation->email }}</flux:heading>
                            <flux:text size="sm">{{ ucfirst($invitation->role) }} · Expires {{ optional($invitation->expires_at)->diffForHumans() }}</flux:text>
                        </div>
                    @empty
                        <div class="p-4">
                            <flux:text>No pending invitations.</flux:text>
                        </div>
                    @endforelse
                </div>
            </flux:card>

            <flux:card class="p-0 overflow-hidden">
                <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900/50">
                    <flux:heading level="2">Recently Accepted</flux:heading>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($acceptedInvitations as $invitation)
                        <div class="p-4">
                            <flux:heading level="3">{{ $invitation->email }}</flux:heading>
                            <flux:text size="sm">Accepted {{ optional($invitation->accepted_at)->diffForHumans() }}</flux:text>
                        </div>
                    @empty
                        <div class="p-4">
                            <flux:text>No accepted invitations yet.</flux:text>
                        </div>
                    @endforelse
                </div>
            </flux:card>
        </div>
    </div>
</x-layouts::app>
