<div class="min-h-screen bg-gray-50 py-8 dark:bg-zinc-900">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $isEditing ? __('Edit Training Goal') : __('Set a New Training Goal') }}
                    </h1>
                    <p class="text-gray-600 dark:text-zinc-400">
                        {{ __('Build your mission plan and define measurable outcomes.') }}
                    </p>
                </div>
                <a
                    href="{{ route('training.dashboard') }}"
                    wire:navigate
                    class="text-gray-500 transition hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                    aria-label="{{ __('Back to training dashboard') }}"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="mb-8">
            <div class="flex items-center justify-between">
                @foreach([1 => __('Basics'), 2 => __('Plan'), 3 => __('Partner'), 4 => __('Milestones')] as $current => $label)
                    <div class="flex items-center {{ $current < 4 ? 'flex-1' : '' }}">
                        <button
                            wire:click="$set('step', {{ $current }})"
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-full border-2 text-sm transition-all duration-200
                                {{ $step === $current ? 'border-blue-500 bg-blue-500 text-white' : '' }}
                                {{ $step > $current ? 'border-green-500 bg-green-500 text-white' : '' }}
                                {{ $step < $current ? 'border-gray-300 bg-white text-gray-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-500' : '' }}"
                        >
                            @if($step > $current)
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <span class="font-semibold">{{ $current }}</span>
                            @endif
                        </button>
                        <span class="ml-3 text-sm font-medium {{ $step >= $current ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-zinc-400' }}">
                            {{ $label }}
                        </span>
                    </div>
                    @if($current < 4)
                        <div class="mx-4 h-0.5 flex-1 {{ $step > $current ? 'bg-green-500' : 'bg-gray-300 dark:bg-zinc-700' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-lg dark:bg-zinc-800">
            <form wire:submit="save">
                @if($step === 1)
                    <div class="space-y-6 p-6 sm:p-8">
                        <div class="mb-2">
                            <h2 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                                {{ __('Define your goal') }}
                            </h2>
                            <p class="text-gray-600 dark:text-zinc-400">
                                {{ __('Set the training mission, focus area, and timeline.') }}
                            </p>
                        </div>

                        <flux:input wire:model="title" :label="__('Goal Title')" placeholder="e.g. Master Laravel Eloquent Relationships" required />

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <flux:select wire:model="category" :label="__('Category')" required>
                                <option value="">{{ __('Select a category') }}</option>
                                @foreach($this->categories as $choice)
                                    <option value="{{ $choice->value }}">{{ $choice->icon() }} {{ $choice->label() }}</option>
                                @endforeach
                            </flux:select>

                            <flux:select wire:model="focus_area_id" :label="__('Focus Area')" required>
                                <option value="">{{ __('Select a focus area') }}</option>
                                @foreach($this->focusAreas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <flux:input type="date" wire:model="start_date" :label="__('Start Date')" required />
                            <flux:input type="date" wire:model="target_date" :label="__('Target Date')" required />
                        </div>
                    </div>
                @endif

                @if($step === 2)
                    <div class="space-y-6 p-6 sm:p-8">
                        <div class="mb-2">
                            <h2 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                                {{ __('Create the execution plan') }}
                            </h2>
                            <p class="text-gray-600 dark:text-zinc-400">
                                {{ __('Describe the scope and define how success will be measured.') }}
                            </p>
                        </div>

                        <flux:textarea wire:model="description" :label="__('Detailed Overview')" placeholder="What exactly do you want to achieve?" required />
                        <flux:textarea wire:model="success_criteria" :label="__('Success Criteria')" placeholder="How will you know you've achieved this goal?" required />
                        <flux:checkbox wire:model="is_public" :label="__('Make this goal visible to the team')" description="{{ __('Team leads can always see goals.') }}" />
                    </div>
                @endif

                @if($step === 3)
                    <div class="space-y-6 p-6 text-center sm:p-8">
                        <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-700">
                            <flux:icon icon="users" class="size-8 text-zinc-500 dark:text-zinc-300" />
                        </div>
                        <div>
                            <h2 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                                {{ __('Add accountability support') }}
                            </h2>
                            <p class="mx-auto max-w-xl text-gray-600 dark:text-zinc-400">
                                {{ __('Invite a teammate to review progress and keep momentum. This step is optional.') }}
                            </p>
                        </div>

                        <flux:select wire:model="accountability_partner_id" :label="__('Select a Team Member')">
                            <option value="">{{ __('No partner for now') }}</option>
                            @foreach($this->users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </flux:select>

                        <p class="text-xs text-amber-600 dark:text-amber-400">
                            {{ __('They will receive a partner request after you launch this goal.') }}
                        </p>
                    </div>
                @endif

                @if($step === 4)
                    <div class="space-y-6 p-6 sm:p-8">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <div>
                                <h2 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                                    {{ __('Break the mission into milestones') }}
                                </h2>
                                <p class="text-gray-600 dark:text-zinc-400">
                                    {{ __('Add small, measurable checkpoints to keep progress visible.') }}
                                </p>
                            </div>
                            <flux:button size="sm" variant="ghost" icon="plus" type="button" wire:click="addMilestone">
                                {{ __('Add Milestone') }}
                            </flux:button>
                        </div>

                        <div class="space-y-4">
                            @foreach($milestones as $index => $m)
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-zinc-700 dark:bg-zinc-900/50">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-1 space-y-4">
                                            <flux:input wire:model="milestones.{{ $index }}.title" placeholder="e.g. Complete the basics course" />
                                            <flux:input type="date" wire:model="milestones.{{ $index }}.target_date" :label="__('Target completion')" />
                                        </div>
                                        <flux:button icon="x" variant="ghost" size="sm" type="button" wire:click="removeMilestone({{ $index }})" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-900 sm:px-8">
                    <div class="flex items-center justify-between">
                        @if($step > 1)
                            <flux:button variant="ghost" type="button" wire:click="previousStep">
                                {{ __('Back') }}
                            </flux:button>
                        @else
                            <div></div>
                        @endif

                        @if($step < 4)
                            <flux:button variant="primary" type="button" wire:click="nextStep">
                                {{ __('Next Step') }}
                            </flux:button>
                        @else
                            <flux:button variant="primary" type="submit">
                                {{ $isEditing ? __('Update Goal') : __('Launch Goal') }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
