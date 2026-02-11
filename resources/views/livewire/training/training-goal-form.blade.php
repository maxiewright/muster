<div class="max-w-2xl mx-auto py-8">
    <div class="mb-8">
        <flux:heading size="xl" level="1">{{ $isEditing ? __('Edit Training Goal') : __('Set a New Training Goal') }}</flux:heading>
        <flux:subheading>{{ __('Define what you want to learn and how you\'ll measure success.') }}</flux:subheading>
    </div>

    <flux:card class="p-6">
        {{-- Step Progress --}}
        <div class="flex items-center justify-between mb-8 px-4">
            @foreach([1, 2, 3, 4] as $s)
                <div class="flex flex-col items-center">
                    <div class="size-8 rounded-full flex items-center justify-center {{ $step >= $s ? 'bg-blue-600 text-white' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-500' }}">
                        {{ $s }}
                    </div>
                    <span class="text-xs mt-1 {{ $step >= $s ? 'text-blue-600 font-bold' : 'text-zinc-500' }}">
                        @if($s == 1) {{ __('Basics') }} @elseif($s == 2) {{ __('Plan') }} @elseif($s == 3) {{ __('Partner') }} @else {{ __('Milestones') }} @endif
                    </span>
                </div>
                @if($s < 4)
                    <div class="flex-1 h-px {{ $step > $s ? 'bg-blue-600' : 'bg-zinc-200 dark:bg-zinc-700' }} mx-2 mb-4"></div>
                @endif
            @endforeach
        </div>

        <form wire:submit="save">
            {{-- Step 1: Basics --}}
            <div x-show="$wire.step === 1" class="space-y-6">
                <flux:input wire:model="title" :label="__('Goal Title')" placeholder="e.g. Master Laravel Eloquent Relationships" required />
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input type="date" wire:model="start_date" :label="__('Start Date')" required />
                    <flux:input type="date" wire:model="target_date" :label="__('Target Date')" required />
                </div>
            </div>

            {{-- Step 2: Plan --}}
            <div x-show="$wire.step === 2" class="space-y-6">
                <flux:textarea wire:model="description" :label="__('Detailed Overview')" placeholder="What exactly do you want to achieve?" required />
                <flux:textarea wire:model="success_criteria" :label="__('Success Criteria')" placeholder="How will you know you've achieved this goal?" required />
                <flux:checkbox wire:model="is_public" :label="__('Make this goal visible to the team')" description="Team leads can always see goals." />
            </div>

            {{-- Step 3: Partner --}}
            <div x-show="$wire.step === 3" class="space-y-6 text-center py-4">
                <flux:icon icon="users" class="size-16 mx-auto text-zinc-300 mb-4" />
                <flux:heading>{{ __('Accountability Partner (Optional)') }}</flux:heading>
                <flux:subheading class="mb-6 max-w-sm mx-auto">
                    {{ __('Having a partner increases your chances of success. They can verify your progress and keep you motivated.') }}
                </flux:subheading>

                <flux:select wire:model="accountability_partner_id" :label="__('Select a Team Member')">
                    <option value="">{{ __('No partner for now') }}</option>
                    @foreach($this->users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </flux:select>

                <p class="text-xs text-amber-600 dark:text-amber-400 mt-4">
                    {{ __('Note: They will receive a request to be your partner.') }}
                </p>
            </div>

            {{-- Step 4: Milestones --}}
            <div x-show="$wire.step === 4" class="space-y-6">
                <div class="flex justify-between items-center">
                    <flux:heading>{{ __('Break it Down') }}</flux:heading>
                    <flux:button size="sm" variant="ghost" icon="plus" wire:click="addMilestone">
                        {{ __('Add Milestone') }}
                    </flux:button>
                </div>
                <flux:subheading>{{ __('Add small, measurable steps to reach your goal.') }}</flux:subheading>

                <div class="space-y-4">
                    @foreach($milestones as $index => $m)
                        <flux:card class="p-4 bg-zinc-50 dark:bg-zinc-900/50">
                            <div class="flex gap-4 items-start">
                                <div class="flex-1 space-y-4">
                                    <flux:input wire:model="milestones.{{ $index }}.title" placeholder="e.g. Complete the basics course" />
                                    <flux:input type="date" wire:model="milestones.{{ $index }}.target_date" :label="__('Target completion')" />
                                </div>
                                <flux:button icon="x" variant="ghost" size="sm" wire:click="removeMilestone({{ $index }})" />
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-between mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                @if($step > 1)
                    <flux:button variant="ghost" wire:click="previousStep">{{ __('Back') }}</flux:button>
                @else
                    <div></div>
                @endif

                @if($step < 4)
                    <flux:button variant="primary" wire:click="nextStep">{{ __('Next Step') }}</flux:button>
                @else
                    <flux:button variant="primary" type="submit">{{ $isEditing ? __('Update Goal') : __('Launch Goal') }}</flux:button>
                @endif
            </div>
        </form>
    </flux:card>
</div>
