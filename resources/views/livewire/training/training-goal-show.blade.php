<div class="max-w-4xl mx-auto py-8">
    <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl">{{ $goal->category->icon() }}</span>
                <flux:heading size="xl" level="1">{{ $goal->title }}</flux:heading>
                <flux:badge :color="$goal->status->color()">{{ $goal->status->label() }}</flux:badge>
            </div>
            <flux:subheading size="lg">
                {{ $goal->category->label() }} • {{ $goal->focusArea?->name }} • {{ __('By') }} {{ $goal->user->name }}
            </flux:subheading>
        </div>
        <div class="flex gap-2">
            @if($this->canEdit)
                <flux:button variant="ghost" icon="pencil" href="{{ route('training.goals.edit', $goal->slug) }}">
                    {{ __('Edit') }}
                </flux:button>
            @endif
            @if($this->isOwner && $goal->status === \App\Enums\TrainingGoalStatus::Active)
                <flux:button variant="primary" icon="plus" href="{{ route('training.goals.checkin', $goal->slug) }}">
                    {{ __('Log Progress') }}
                </flux:button>
            @endif
        </div>
    </div>

    @if($goal->partner_status === \App\Enums\PartnerStatus::Pending && $this->isPartner)
        <flux:card class="mb-8 border-amber-200 bg-amber-50 dark:bg-amber-900/10 p-6">
            <div class="flex items-center gap-4 mb-4">
                <flux:icon icon="bell-ring" class="text-amber-500 size-6" />
                <div>
                    <flux:heading level="3">{{ __('Partner Request') }}</flux:heading>
                    <flux:text>{{ $goal->user->name }} {{ __('has invited you to be their accountability partner.') }}</flux:text>
                </div>
            </div>
            <div class="flex gap-3">
                <flux:button variant="primary" wire:click="acceptPartnerRequest">{{ __('Accept Request') }}</flux:button>
                <flux:button variant="ghost" wire:click="declinePartnerRequest">{{ __('Decline') }}</flux:button>
            </div>
        </flux:card>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            {{-- Goal Overview --}}
            <flux:card class="p-6">
                <flux:heading level="2" class="mb-4">{{ __('Overview') }}</flux:heading>
                <div class="prose dark:prose-invert max-w-none">
                    <p>{{ $goal->description }}</p>
                </div>

                <flux:separator variant="subtle" class="my-6" />

                <flux:heading level="3" class="mb-2">{{ __('Success Criteria') }}</flux:heading>
                <div class="prose dark:prose-invert max-w-none">
                    <p>{{ $goal->success_criteria }}</p>
                </div>
            </flux:card>

            {{-- Milestones --}}
            <div id="milestones">
                <flux:heading level="2" class="mb-4 flex items-center justify-between">
                    {{ __('Milestones') }}
                    <span class="text-sm font-normal text-zinc-500">{{ $goal->completed_milestones_count }}/{{ $goal->total_milestones_count }} {{ __('Completed') }}</span>
                </flux:heading>
                <div class="space-y-4">
                    @foreach($goal->milestones as $milestone)
                        <flux:card class="p-4 flex items-start justify-between gap-4">
                            <div class="flex gap-4">
                                <div class="mt-1">
                                    @if($milestone->status === \App\Enums\MilestoneStatus::Verified)
                                        <flux:icon icon="badge-check" class="text-green-500 size-6" />
                                    @elseif($milestone->status === \App\Enums\MilestoneStatus::Completed)
                                        <flux:icon icon="circle-check" class="text-blue-500 size-6" />
                                    @elseif($milestone->is_overdue)
                                        <flux:icon icon="circle-alert" class="text-red-500 size-6" />
                                    @else
                                        <flux:icon icon="circle" class="text-zinc-300 size-6" />
                                    @endif
                                </div>
                                <div>
                                    <flux:heading size="sm" class="{{ $milestone->status === \App\Enums\MilestoneStatus::Verified ? 'line-through text-zinc-400' : '' }}">
                                        {{ $milestone->title }}
                                    </flux:heading>
                                    @if($milestone->target_date)
                                        <flux:text size="sm" class="{{ $milestone->is_overdue ? 'text-red-500' : '' }}">
                                            {{ __('Target:') }} {{ $milestone->target_date->format('M d, Y') }}
                                        </flux:text>
                                    @endif
                                </div>
                            </div>

                            @if($this->canVerify && $milestone->status === \App\Enums\MilestoneStatus::Completed)
                                <flux:button size="sm" variant="primary" wire:click="verifyMilestone({{ $milestone->id }})">
                                    {{ __('Verify') }}
                                </flux:button>
                            @endif
                        </flux:card>
                    @endforeach
                </div>
            </div>

            {{-- Progress Feed --}}
            <div>
                <flux:heading level="2" class="mb-4">{{ __('Updates & Feed') }}</flux:heading>
                <div class="space-y-6">
                    @forelse($goal->checkins as $checkin)
                        <div class="relative flex gap-4">
                            @if(!$loop->last)
                                <div class="absolute left-6 top-10 bottom-0 w-px bg-zinc-200 dark:bg-zinc-800"></div>
                            @endif
                            <img src="{{ $checkin->user->profileImageUrl('thumb') }}" class="size-12 rounded-full relative z-10" alt="">
                            <div class="flex-1">
                                <flux:card class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <flux:heading size="sm">{{ $checkin->user->name }}</flux:heading>
                                            <flux:subheading size="xs">{{ $checkin->created_at->diffForHumans() }}</flux:subheading>
                                        </div>
                                        <span class="text-xl">{{ $checkin->confidence_level?->emoji() }}</span>
                                    </div>
                                    <div class="prose dark:prose-invert prose-sm max-w-none mb-4">
                                        <p>{{ $checkin->progress_update }}</p>
                                        @if($checkin->learnings)
                                            <p class="font-bold text-xs mt-2 text-zinc-500 uppercase">{{ __('Learnings:') }}</p>
                                            <p>{{ $checkin->learnings }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-zinc-500">
                                        <div class="flex items-center gap-1">
                                            <flux:icon icon="clock" class="size-3" />
                                            {{ $checkin->minutes_logged }}m
                                        </div>
                                        @if($checkin->milestone)
                                            <div class="flex items-center gap-1">
                                                <flux:icon icon="flag" class="size-3" />
                                                {{ $checkin->milestone->title }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Partner Feedback --}}
                                    @if($checkin->partner_feedback)
                                        <div class="mt-4 p-3 bg-zinc-50 dark:bg-zinc-900/50 rounded-lg border-l-2 border-zinc-300">
                                            <div class="flex items-center gap-2 mb-1">
                                                <img src="{{ $checkin->feedbackProvider->profileImageUrl('thumb') }}" class="size-4 rounded-full" alt="">
                                                <flux:text size="xs" class="font-bold">{{ $checkin->feedbackProvider->name }}</flux:text>
                                            </div>
                                            <flux:text size="xs">{{ $checkin->partner_feedback }}</flux:text>
                                        </div>
                                    @elseif($this->isPartner && $this->goal->partner_status === \App\Enums\PartnerStatus::Accepted)
                                        <div class="mt-4">
                                            <flux:button size="xs" variant="ghost" icon="message-circle">
                                                {{ __('Leave Feedback') }}
                                            </flux:button>
                                        </div>
                                    @endif
                                </flux:card>
                            </div>
                        </div>
                    @empty
                        <flux:text class="text-center py-8 text-zinc-500">{{ __('No updates logged yet.') }}</flux:text>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Progress Wheel --}}
            <flux:card class="p-6 text-center">
                <flux:heading level="3" class="mb-6">{{ __('Goal Progress') }}</flux:heading>
                <div class="relative size-32 mx-auto mb-6 flex items-center justify-center">
                    <svg class="size-full" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="16" fill="none" class="stroke-zinc-100 dark:stroke-zinc-800" stroke-width="3"></circle>
                        <circle cx="18" cy="18" r="16" fill="none" class="stroke-blue-600" stroke-width="3" 
                                stroke-dasharray="{{ $goal->progress_percentage }}, 100" 
                                stroke-linecap="round" transform="rotate(-90 18 18)"></circle>
                    </svg>
                    <span class="absolute text-2xl font-bold">{{ $goal->progress_percentage }}%</span>
                </div>
                
                @if($goal->status === \App\Enums\TrainingGoalStatus::Active && $goal->progress_percentage === 100 && $this->isOwner)
                    <flux:button variant="primary" class="w-full" wire:click="verifyGoal">
                        {{ __('Final Completion') }}
                    </flux:button>
                @elseif($goal->status === \App\Enums\TrainingGoalStatus::Completed && $this->canVerify)
                     <flux:button variant="primary" color="green" class="w-full" wire:click="verifyGoal">
                        {{ __('Verify Completion') }}
                    </flux:button>
                @endif
            </flux:card>

            {{-- Dates & Time --}}
            <flux:card class="p-6 space-y-4">
                <div>
                    <flux:text size="sm" class="block text-zinc-500">{{ __('Start Date') }}</flux:text>
                    <flux:text>{{ $goal->start_date->format('M d, Y') }}</flux:text>
                </div>
                <div>
                    <flux:text size="sm" class="block text-zinc-500">{{ __('Target Date') }}</flux:text>
                    <flux:text class="{{ $goal->is_overdue ? 'text-red-500 font-bold' : '' }}">
                        {{ $goal->target_date->format('M d, Y') }}
                    </flux:text>
                    @if($goal->status === \App\Enums\TrainingGoalStatus::Active)
                        <flux:subheading size="xs">{{ $goal->days_remaining }} {{ __('days remaining') }}</flux:subheading>
                    @endif
                </div>
                <flux:separator variant="subtle" />
                <div>
                    <flux:text size="sm" class="block text-zinc-500">{{ __('Total Time Logged') }}</flux:text>
                    <flux:text size="lg" class="font-bold">{{ $goal->logged_hours }}h</flux:text>
                </div>
            </flux:card>

            {{-- Partner Information --}}
            <flux:card class="p-6">
                <flux:heading level="3" class="mb-4">{{ __('Accountability Partner') }}</flux:heading>
                @if($goal->partner)
                    <div class="flex items-center gap-4 mb-4">
                        <img src="{{ $goal->partner->profileImageUrl('thumb') }}" class="size-10 rounded-full" alt="">
                        <div>
                            <flux:text class="font-bold">{{ $goal->partner->name }}</flux:text>
                            <flux:badge size="sm" :color="$goal->partner_status->color()">{{ $goal->partner_status->label() }}</flux:badge>
                        </div>
                    </div>
                    @if($goal->partner_status === \App\Enums\PartnerStatus::Pending && $this->isOwner)
                         <flux:subheading size="xs">{{ __('Waiting for partner to accept request...') }}</flux:subheading>
                    @endif
                @else
                    <flux:text size="sm" class="text-zinc-500 italic">{{ __('No partner assigned to this goal.') }}</flux:text>
                @endif
            </flux:card>
        </div>
    </div>
</div>
