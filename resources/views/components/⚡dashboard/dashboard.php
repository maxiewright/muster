<?php

use App\Models\Standup;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        $user = auth()->user();

        return view('components.⚡dashboard.dashboard', [
            'todaysStandups' => Standup::query()
                ->with(['user', 'focusAreas', 'standupTasks.task'])
                ->whereDate('date', today())
                ->latest()
                ->get(),

            'upcomingEvents' => \App\Models\Event::with(['user', 'type'])
                ->where('starts_at', '>=', now())
                ->where('starts_at', '<=', now()->addDays(7))
                ->orderBy('starts_at')
                ->limit(5)
                ->get(),

            'myStandup' => $user->todaysStandup(),
        ]);
    }
};
