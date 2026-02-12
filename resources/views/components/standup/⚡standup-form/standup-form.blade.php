<div class="min-h-screen bg-gray-50 dark:bg-zinc-900 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $isEditing ? 'Edit Check-in' : 'Daily Muster' }}
                    </h1>
                    <p class="text-gray-600 dark:text-zinc-400">
                        {{ now()->format('l, F j, Y') }}
                    </p>
                </div>
                <a href="{{ route('standups') }}" wire:navigate
                   class="text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- Progress Steps --}}
        <div class="mb-8">
            <div class="flex items-center justify-between">
                @foreach([1 => 'Yesterday', 2 => 'Today', 3 => 'Wrap Up'] as $step => $label)
                    <div class="flex items-center {{ $step < 3 ? 'flex-1' : '' }}">
                        <button
                            wire:click="goToStep({{ $step }})"
                            type="button"
                            class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-200
                                {{ $currentStep === $step ? 'border-blue-500 bg-blue-500 text-white' : '' }}
                                {{ $currentStep > $step ? 'border-green-500 bg-green-500 text-white' : '' }}
                                {{ $currentStep < $step ? 'border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-400 dark:text-zinc-500' : '' }}"
                        >
                            @if($currentStep > $step)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            @else
                                <span class="font-semibold">{{ $step }}</span>
                            @endif
                        </button>
                        <span class="ml-3 text-sm font-medium {{ $currentStep >= $step ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-zinc-400' }}">
                            {{ $label }}
                        </span>
                    </div>
                    @if($step < 3)
                        <div class="flex-1 h-0.5 mx-4 {{ $currentStep > $step ? 'bg-green-500' : 'bg-gray-300 dark:bg-zinc-700' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Main Card --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg overflow-hidden">

            {{-- Step 1: Yesterday Review --}}
            @if($currentStep === 1)
                <div class="p-6 sm:p-8">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            What did you work on yesterday?
                        </h2>
                        <p class="text-gray-600 dark:text-zinc-400">
                            Review your tasks and mark what you completed or need to carry over.
                        </p>
                    </div>

                    @if($this->yesterdayTasks->count() > 0)
                        <div class="space-y-3">
                            @foreach($this->yesterdayTasks as $task)
                                @php
                                    $isCompleted = in_array($task->id, $completedTaskIds);
                                    $isCarriedOver = in_array($task->id, $carriedOverTaskIds);
                                @endphp
                                <div wire:key="yesterday-{{ $task->id }}"
                                     class="p-4 rounded-lg border-2 transition-all duration-200
                                         {{ $isCompleted ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : '' }}
                                         {{ $isCarriedOver && !$isCompleted ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : '' }}
                                         {{ !$isCompleted && !$isCarriedOver ? 'border-gray-200 dark:border-zinc-700 hover:border-gray-300 dark:hover:border-zinc-600' : '' }}">

                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 pt-0.5">
                                            <flux:checkbox :checked="$isCompleted"
                                                          wire:click="toggleCompleted({{ $task->id }})"
                                                          class="!cursor-pointer"
                                                          aria-label="Mark {{ $task->title }} as {{ $isCompleted ? 'not complete' : 'complete' }}" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-2 mb-1">
                                                <h4 class="font-medium text-gray-900 dark:text-white {{ $isCompleted ? 'line-through opacity-70' : '' }}">
                                                    {{ $task->title }}
                                                </h4>
                                                <span class="flex-shrink-0 px-2 py-0.5 text-xs rounded-full {{ $task->status->color() }}">
                                                    {{ $task->status->label() }}
                                                </span>
                                            </div>
                                            @if($task->description)
                                                <p class="text-sm text-gray-600 dark:text-zinc-400 line-clamp-2">
                                                    {{ $task->description }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-zinc-700">
                                        <button wire:click="toggleCompleted({{ $task->id }})"
                                                type="button"
                                                class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm rounded-lg transition
                                                    {{ $isCompleted
                                                        ? 'bg-green-500 text-white'
                                                        : 'bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-green-100 dark:hover:bg-green-900/30' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ $isCompleted ? 'Completed' : 'Mark Complete' }}
                                        </button>

                                        <button wire:click="toggleCarriedOver({{ $task->id }})"
                                                type="button"
                                                class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm rounded-lg transition
                                                    {{ $isCarriedOver && !$isCompleted
                                                        ? 'bg-amber-500 text-white'
                                                        : 'bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-amber-100 dark:hover:bg-amber-900/30' }}"
                                            {{ $isCompleted ? 'disabled' : '' }}>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                            {{ $isCarriedOver ? 'Carrying Over' : 'Carry Over' }}
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Summary --}}
                        @if(count($completedTaskIds) > 0 || count($carriedOverTaskIds) > 0)
                            <div class="mt-4 flex flex-wrap gap-3">
                                @if(count($completedTaskIds) > 0)
                                    <div class="flex items-center gap-2 px-3 py-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-sm font-medium text-green-800 dark:text-green-200">
                                            {{ count($completedTaskIds) }} completed
                                        </span>
                                    </div>
                                @endif
                                @if(count($carriedOverTaskIds) > 0)
                                    <div class="flex items-center gap-2 px-3 py-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                        <span class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                            {{ count($carriedOverTaskIds) }} carrying over
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endif

                    @else
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-zinc-700 mb-4">
                                <svg class="w-8 h-8 text-gray-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No tasks from yesterday</h3>
                            <p class="text-gray-600 dark:text-zinc-400">
                                You didn't have any in-progress tasks or planned items.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Step 1 Footer --}}
                <div class="px-6 sm:px-8 py-4 bg-gray-50 dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-700">
                    <div class="flex justify-end">
                        <button wire:click="nextStep" type="button"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                            Continue to Today's Plan
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 2: Today's Plan --}}
            @if($currentStep === 2)
                <div class="p-6 sm:p-8">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            What will you work on today?
                        </h2>
                        <p class="text-gray-600 dark:text-zinc-400">
                            Select tasks from your backlog or create new ones.
                        </p>
                    </div>

                    {{-- Quick Create Task --}}
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <h3 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-3">Quick Create Task</h3>
                        <form wire:submit="createQuickTask" class="flex flex-col sm:flex-row gap-2">
                            <flux:input
                                wire:model="newTaskTitle"
                                placeholder="Enter task title..."
                                class="flex-1 min-w-0"
                            />
                            <flux:button type="submit" variant="primary" class="min-h-[44px] sm:min-h-0">
                                <flux:icon name="plus" class="size-4 -ml-0.5" />
                                Add
                            </flux:button>
                        </form>
                        @error('newTaskTitle')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Started (ongoing) Tasks --}}
                    @if(count($ongoingTaskIds) > 0)
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-zinc-300 mb-3 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                                </span>
                                Started ({{ count($ongoingTaskIds) }})
                            </h3>
                            <div class="space-y-2">
                                @foreach($this->selectedOngoingTasks as $task)
                                    <div wire:key="ongoing-{{ $task->id }}"
                                         class="flex items-center justify-between p-3 rounded-lg border border-green-300 bg-green-50 dark:border-green-800 dark:bg-green-900/20">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm {{ $task->priority->color() }}">{{ $task->priority->icon() }}</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $task->title }}</span>
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-200">Working on it</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Planned Tasks for Today (not yet started) --}}
                    @if(count($plannedTaskIds) > 0)
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-zinc-300 mb-3">
                                Today's Tasks ({{ count($plannedTaskIds) }})
                            </h3>
                            <div class="space-y-2">
                                @foreach($this->selectedPlannedTasks as $task)
                                    @php $isBlocked = in_array($task->id, $blockedTaskIds); @endphp
                                    <div wire:key="planned-{{ $task->id }}"
                                         class="flex items-center justify-between p-3 rounded-lg border transition cursor-grab active:cursor-grabbing hover:shadow-sm hover:-translate-y-[1px]
                                             {{ $isBlocked ? 'border-red-300 bg-red-50 dark:border-red-800 dark:bg-red-900/20' : 'border-blue-300 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20' }}">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm {{ $task->priority->color() }}">{{ $task->priority->icon() }}</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $task->title }}</span>
                                            @if($isBlocked)
                                                <span class="px-2 py-0.5 text-xs rounded-full bg-red-200 text-red-800 dark:bg-red-800 dark:text-red-200">
                                                    🚧 Blocked
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button wire:click="startTaskToOngoing({{ $task->id }})"
                                                    type="button"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-lg bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 hover:bg-green-200 dark:hover:bg-green-800/50 transition"
                                                    title="Start working on this task">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                                                Start
                                            </button>
                                            <button wire:click="toggleBlocked({{ $task->id }})"
                                                    type="button"
                                                    class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 transition"
                                                    title="{{ $isBlocked ? 'Remove blocker' : 'Mark as blocked' }}">
                                                <span class="text-sm">{{ $isBlocked ? '🚧' : '⚠️' }}</span>
                                            </button>
                                            <button wire:click="removeFromPlanned({{ $task->id }})"
                                                    type="button"
                                                    class="p-1.5 rounded text-gray-400 hover:text-red-600 hover:bg-gray-200 dark:hover:bg-zinc-600 transition"
                                                    title="Remove from today">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Search & Backlog Tasks --}}
                    <div class="mb-4">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <flux:input
                                wire:model.live.debounce.300ms="taskSearch"
                                placeholder="Search your backlog..."
                                class="w-full pl-10"
                            />
                        </div>
                    </div>

                    @if($this->backlogTasks->count() > 0)
                        <div class="space-y-2">
                            @foreach($this->backlogTasks as $task)
                                <div wire:key="backlog-{{ $task->id }}"
                                     wire:click="togglePlanned({{ $task->id }})"
                                     class="flex items-center gap-4 p-3 rounded-lg border border-gray-200 dark:border-zinc-700 hover:border-blue-300 dark:hover:border-blue-700 cursor-pointer transition">
                                    <div class="flex-shrink-0">
                                        <div class="w-5 h-5 rounded border-2 border-gray-300 dark:border-zinc-600 flex items-center justify-center">
                                            @if(in_array($task->id, $plannedTaskIds))
                                                <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm">{{ $task->priority->icon() }}</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $task->title }}</span>
                                        </div>
                                        @if($task->description)
                                            <p class="text-sm text-gray-500 dark:text-zinc-400 truncate">{{ $task->description }}</p>
                                        @endif
                                    </div>
                                    @if($task->due_date)
                                        <span class="text-xs {{ $task->isOverdue() ? 'text-red-600' : 'text-gray-500' }}">
                                            {{ $task->due_date->format('M j') }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500 dark:text-zinc-400">
                            @if($taskSearch)
                                <p>No tasks matching "{{ $taskSearch }}"</p>
                            @else
                                <p>Your backlog is empty. Create a task above!</p>
                            @endif
                        </div>
                    @endif

                </div>

                {{-- Step 2 Footer --}}
                <div class="px-6 sm:px-8 py-4 bg-gray-50 dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-700">
                    <div class="flex justify-between">
                        <button wire:click="previousStep" type="button"
                                class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-lg font-medium transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Back
                        </button>
                        <button wire:click="nextStep" type="button"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                            Continue to Wrap Up
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 3: Wrap Up --}}
            @if($currentStep === 3)
                <div class="p-6 sm:p-8">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            Wrap Up
                        </h2>
                        <p class="text-gray-600 dark:text-zinc-400">
                            Share any blockers and let us know how you're feeling.
                        </p>
                    </div>

                    {{-- Summary --}}
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-zinc-900 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-zinc-300 mb-3">Check-in Summary</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                            <div class="text-center p-3 bg-white dark:bg-zinc-800 rounded-lg">
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $this->summaryStats['completed'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-zinc-400">Completed</p>
                            </div>
                            <div class="text-center p-3 bg-white dark:bg-zinc-800 rounded-lg">
                                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $this->summaryStats['ongoing'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-zinc-400">Started</p>
                            </div>
                            <div class="text-center p-3 bg-white dark:bg-zinc-800 rounded-lg">
                                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $this->summaryStats['carried_over'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-zinc-400">Carried Over</p>
                            </div>
                            <div class="text-center p-3 bg-white dark:bg-zinc-800 rounded-lg">
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $this->summaryStats['planned'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-zinc-400">Planned</p>
                            </div>
                            <div class="text-center p-3 bg-white dark:bg-zinc-800 rounded-lg">
                                <p class="text-2xl font-bold {{ $this->summaryStats['blocked'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">
                                    {{ $this->summaryStats['blocked'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-zinc-400">Blocked</p>
                            </div>
                        </div>

                    </div>

                    {{-- Blockers --}}
                    <div class="mb-6">
                        <flux:field>
                            <flux:label>Blockers <span class="text-gray-400">(optional)</span></flux:label>
                            <flux:text size="sm" variant="subtle">
                                Any impediments or challenges preventing progress?
                            </flux:text>
                            <flux:textarea
                                wire:model="blockers"
                                rows="3"
                                placeholder="Describe any blockers you're facing..."
                            />
                            <flux:error name="blockers" />
                        </flux:field>
                    </div>

                    {{-- Mood Selector --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-3">
                            How are you feeling today?
                        </label>
                        <div class="grid grid-cols-5 gap-3">
                            @foreach($moods as $moodOption)
                                <button wire:click="$set('mood', '{{ $moodOption->value }}')"
                                        type="button"
                                        class="flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all duration-200 hover:scale-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-zinc-900
                                            {{ $mood === $moodOption->value
                                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 shadow-md'
                                                : 'border-gray-200 dark:border-zinc-700 hover:border-gray-300 dark:hover:border-zinc-600' }}">
                                    <span class="text-3xl mb-2">{{ $moodOption->emoji() }}</span>
                                    <span class="text-xs font-medium text-gray-700 dark:text-zinc-300 text-center">
                                        {{ $moodOption->label() }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                        @error('mood')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Step 3 Footer --}}
                <div class="px-6 sm:px-8 py-4 bg-gray-50 dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-700">
                    <div class="flex justify-between items-center">
                        <button wire:click="previousStep" type="button"
                                class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-lg font-medium transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Back
                        </button>
                        <button wire:click="submitStandup"
                                type="button"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-6 py-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white rounded-lg font-medium transition">
                            <span wire:loading.remove wire:target="submitStandup">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <span wire:loading wire:target="submitStandup">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="submitStandup">
                                {{ $isEditing ? 'Update Check-in' : 'Submit Check-in' }}
                            </span>
                            <span wire:loading wire:target="submitStandup">Submitting...</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Help Text --}}
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500 dark:text-zinc-500">
                Press <kbd class="px-2 py-1 text-xs bg-gray-100 dark:bg-zinc-800 rounded">ESC</kbd> to cancel
            </p>
        </div>
    </div>

    {{-- Success Modal --}}
    @if($showSuccessModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click.self="closeSuccessModal">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-2xl w-full max-w-sm p-6 text-center transform transition-all max-h-[calc(100dvh-2rem)] overflow-y-auto">
                <div class="text-6xl mb-4">🎉</div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    Check-in Complete!
                </h3>

                @if(count($pointsEarned) > 0)
                    <div class="space-y-2 mb-4 text-left">
                        @foreach($pointsEarned as $earning)
                            <div class="flex items-center justify-between text-sm px-3 py-2 bg-gray-50 dark:bg-zinc-700 rounded-lg">
                                <span class="text-gray-600 dark:text-zinc-400">{{ $earning['reason'] }}</span>
                                <span class="font-bold text-green-600 dark:text-green-400">+{{ $earning['points'] }}</span>
                            </div>
                        @endforeach
                        <div class="flex items-center justify-between font-bold text-lg pt-2 border-t border-gray-200 dark:border-zinc-600">
                            <span class="text-gray-900 dark:text-white">Total</span>
                            <span class="text-green-600 dark:text-green-400">+{{ array_sum(array_column($pointsEarned, 'points')) }}</span>
                        </div>
                    </div>
                @endif

                @if(count($earnedBadges ?? []) > 0)
                    <div class="mb-4">
                        <p class="text-xs font-medium text-amber-600 dark:text-amber-400 mb-2">Badges Earned</p>
                        <div class="flex flex-wrap gap-2 justify-center">
                            @foreach($earnedBadges as $badge)
                                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium"
                                     style="background-color: {{ $badge->color }}20; border: 1px solid {{ $badge->color }}60;">
                                    <span class="text-xl">{{ $badge->icon }}</span>
                                    <div class="text-left">
                                        <span class="text-gray-900 dark:text-white">{{ $badge->name }}</span>
                                        @if($badge->points_reward > 0)
                                            <span class="block text-xs text-green-600 dark:text-green-400">+{{ $badge->points_reward }} pts</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="text-sm text-gray-500 dark:text-zinc-400 mb-6">
                    🔥 Current Streak: <strong>{{ auth()->user()->fresh()->current_streak ?? 1 }} days</strong>
                </div>

                <button wire:click="closeSuccessModal"
                        type="button"
                        class="w-full min-h-[44px] px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition touch-manipulation">
                    Continue to Muster Board
                </button>
            </div>
        </div>
    @endif
</div>
