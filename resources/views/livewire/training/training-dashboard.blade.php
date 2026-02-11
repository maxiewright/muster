<div class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Training & Learning') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Set goals, track progress, and work with accountability partners.') }}</flux:subheading>
        <div class="absolute right-0 top-0">
            <flux:button variant="primary" icon="plus" href="{{ route('training.goals.create') }}">
                {{ __('New Goal') }}
            </flux:button>
        </div>
        <flux:separator variant="subtle" />
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <flux:card class="flex flex-col items-center justify-center py-6">
            <flux:heading size="lg" class="mb-1">{{ $this->stats['completed'] }}</flux:heading>
            <flux:subheading>{{ __('Goals Completed') }}</flux:subheading>
        </flux:card>
        <flux:card class="flex flex-col items-center justify-center py-6">
            <flux:heading size="lg" class="mb-1">{{ $this->stats['partner_count'] }}</flux:heading>
            <flux:subheading>{{ __('As Partner') }}</flux:subheading>
        </flux:card>
        <flux:card class="flex flex-col items-center justify-center py-6">
            <flux:heading size="lg" class="mb-1">{{ $this->stats['hours'] }}h</flux:heading>
            <flux:subheading>{{ __('Hours Logged') }}</flux:subheading>
        </flux:card>
    </div>

    {{-- Pending Partner Requests --}}
    @if($this->pendingRequests->isNotEmpty())
        <div class="mb-8">
            <flux:heading level="2" class="mb-4 flex items-center gap-2">
                <flux:icon icon="bell-ring" class="text-amber-500" />
                {{ __('Partner Requests') }}
            </flux:heading>
            <div class="space-y-4">
                @foreach($this->pendingRequests as $request)
                    <flux:card class="flex items-center justify-between p-4">
                        <div class="flex items-center gap-4">
                            <img src="{{ $request->user->profileImageUrl('thumb') }}" class="size-10 rounded-full" alt="">
                            <div>
                                <flux:heading size="sm">{{ $request->user->name }} {{ __('wants you as an accountability partner') }}</flux:heading>
                                <flux:subheading>{{ __('Goal:') }} {{ $request->title }}</flux:subheading>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="primary" wire:click="$dispatch('openModal', { component: 'training.partner-request-modal', arguments: { goal: {{ $request->id }} } })">
                                {{ __('Review Request') }}
                            </flux:button>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- My Goals --}}
        <div>
            <flux:heading level="2" class="mb-4">{{ __('My Training Goals') }}</flux:heading>
            <div class="space-y-4">
                @forelse($this->activeGoals as $goal)
                    <flux:card class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xl">{{ $goal->category->icon() }}</span>
                                    <flux:heading level="3">{{ $goal->title }}</flux:heading>
                                </div>
                                <flux:subheading>{{ $goal->category->label() }} • {{ $goal->focusArea?->name }}</flux:subheading>
                            </div>
                            <flux:badge :color="$goal->status->color()">{{ $goal->status->label() }}</flux:badge>
                        </div>

                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-1">
                                <flux:text>{{ __('Progress') }}</flux:text>
                                <flux:text>{{ $goal->progress_percentage }}%</flux:text>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $goal->progress_percentage }}%"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <flux:text size="sm" class="block">{{ __('Partner') }}</flux:text>
                                <div class="flex items-center gap-2">
                                    @if($goal->partner)
                                        <img src="{{ $goal->partner->profileImageUrl('thumb') }}" class="size-6 rounded-full" alt="">
                                        <flux:text size="sm">{{ $goal->partner->name }}</flux:text>
                                    @else
                                        <flux:text size="sm" class="text-zinc-500">{{ __('None') }}</flux:text>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <flux:text size="sm" class="block">{{ __('Due Date') }}</flux:text>
                                <flux:text size="sm" class="{{ $goal->is_overdue ? 'text-red-500 font-bold' : '' }}">
                                    {{ $goal->target_date->format('M d, Y') }}
                                    @if($goal->days_remaining > 0)
                                        <span class="text-xs text-zinc-500">({{ $goal->days_remaining }}d left)</span>
                                    @endif
                                </flux:text>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <flux:button size="sm" variant="primary" href="{{ route('training.goals.show', $goal->slug) }}">
                                {{ __('View Details') }}
                            </flux:button>
                            <flux:button size="sm" variant="ghost" href="{{ route('training.goals.checkin', $goal->slug) }}">
                                {{ __('Log Progress') }}
                            </flux:button>
                        </div>
                    </flux:card>
                @empty
                    <flux:card class="p-12 flex flex-col items-center justify-center text-center">
                        <flux:icon icon="trophy" class="size-12 text-zinc-300 mb-4" />
                        <flux:heading>{{ __('No active goals') }}</flux:heading>
                        <flux:subheading class="mb-6">{{ __('Start your learning journey by setting your first training goal.') }}</flux:subheading>
                        <flux:button variant="primary" href="{{ route('training.goals.create') }}">
                            {{ __('Set a Goal') }}
                        </flux:button>
                    </flux:card>
                @endforelse
            </div>
        </div>

        {{-- Partner Goals --}}
        <div>
            <flux:heading level="2" class="mb-4">{{ __('Goals I\'m Supporting') }}</flux:heading>
            <div class="space-y-4">
                @forelse($this->partnerGoals as $goal)
                    <flux:card class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex gap-4">
                                <img src="{{ $goal->user->profileImageUrl('thumb') }}" class="size-10 rounded-full" alt="">
                                <div>
                                    <flux:heading level="3">{{ $goal->title }}</flux:heading>
                                    <flux:subheading>{{ $goal->user->name }}'s goal</flux:subheading>
                                </div>
                            </div>
                            <flux:badge :color="$goal->status->color()">{{ $goal->status->label() }}</flux:badge>
                        </div>

                        <div class="mb-6">
                            <div class="flex justify-between text-sm mb-1">
                                <flux:text>{{ __('Progress') }}</flux:text>
                                <flux:text>{{ $goal->progress_percentage }}%</flux:text>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5">
                                <div class="bg-purple-600 h-2.5 rounded-full" style="width: {{ $goal->progress_percentage }}%"></div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <flux:button size="sm" variant="primary" href="{{ route('training.goals.show', $goal->slug) }}">
                                {{ __('View & Support') }}
                            </flux:button>
                            @php
                                $pendingVerifications = $goal->milestones->where('status', \App\Enums\MilestoneStatus::Completed)->count();
                            @endphp
                            @if($pendingVerifications > 0)
                                <flux:button size="sm" variant="secondary" color="amber" href="{{ route('training.goals.show', $goal->slug) }}#milestones">
                                    {{ $pendingVerifications }} {{ __('to verify') }}
                                </flux:button>
                            @endif
                        </div>
                    </flux:card>
                @empty
                    <flux:card class="p-12 flex flex-col items-center justify-center text-center text-zinc-500">
                        <flux:icon icon="users" class="size-12 mb-4 opacity-20" />
                        <flux:text>{{ __('You aren\'t supporting anyone\'s goals yet.') }}</flux:text>
                    </flux:card>
                @endforelse
            </div>
        </div>
    </div>
</div>
