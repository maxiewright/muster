<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Commander Training Assignments</flux:heading>
            <flux:subheading size="lg">Assign unit-directed training and define accountability partner policy.</flux:subheading>
        </div>

        <flux:button variant="ghost" href="{{ route('training.dashboard') }}" wire:navigate>
            Back to Training
        </flux:button>
    </div>

    <flux:card>
        <form wire:submit="save" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input label="Training Title" wire:model="title" required />
                <flux:select label="Category" wire:model="category">
                    <flux:select.option value="">Select a category</flux:select.option>
                    @foreach($this->categories as $categoryOption)
                        <flux:select.option value="{{ $categoryOption->value }}">{{ $categoryOption->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:select label="Focus Area" wire:model="focus_area_id">
                    <flux:select.option value="">Select a focus area</flux:select.option>
                    @foreach($this->focusAreas as $focusArea)
                        <flux:select.option value="{{ $focusArea->id }}">{{ $focusArea->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <div class="grid gap-4 md:grid-cols-2">
                    <flux:input type="date" label="Start Date" wire:model="start_date" />
                    <flux:input type="date" label="Target Date" wire:model="target_date" />
                </div>
            </div>

            <flux:textarea label="Description" wire:model="description" rows="3" />
            <flux:textarea label="Success Criteria" wire:model="success_criteria" rows="3" />

            <flux:card class="bg-zinc-50 dark:bg-zinc-900/50">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <flux:heading level="3" size="sm">Target Members</flux:heading>
                        <flux:text size="sm">Select specific members, or select the whole unit and exclude by exception.</flux:text>
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <flux:checkbox wire:model.live="assign_to_all_members" />
                        <span>Select all unit members</span>
                    </label>
                </div>

                <div class="mt-4 grid gap-2 md:grid-cols-2">
                    @foreach($this->unitMembers as $member)
                        <label class="flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                            @if($assign_to_all_members)
                                <flux:checkbox wire:model="excluded_member_ids" value="{{ $member->id }}" />
                                <span>Exclude {{ $member->name }}</span>
                            @else
                                <flux:checkbox wire:model="selected_member_ids" value="{{ $member->id }}" />
                                <span>{{ $member->name }}</span>
                            @endif
                        </label>
                    @endforeach
                </div>
            </flux:card>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:select label="Accountability Partner Policy" wire:model.live="partner_policy">
                    <flux:select.option value="optional">No partner required</flux:select.option>
                    <flux:select.option value="member_required">Partner required, member chooses</flux:select.option>
                    <flux:select.option value="commander_locked">Commander assigned partner, locked</flux:select.option>
                    <flux:select.option value="commander_changeable">Commander assigned partner, member may change</flux:select.option>
                </flux:select>

                @if(in_array($partner_policy, ['commander_locked', 'commander_changeable'], true))
                    <flux:select label="Assigned Accountability Partner" wire:model="accountability_partner_id">
                        <flux:select.option value="">Select a partner</flux:select.option>
                        @foreach($this->unitMembers as $member)
                            <flux:select.option value="{{ $member->id }}">{{ $member->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif
            </div>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" class="min-h-[44px] !bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600">
                    Assign Training
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
