<?php

use App\Models\Event;
use App\Models\PartnerNotification;
use App\Models\Standup;
use Livewire\Component;

new class extends Component
{
    public function render(): \Illuminate\Contracts\View\View
    {
        $user = auth()->user();
        $weeklyStart = now()->startOfWeek();
        $weeklyEnd = now()->endOfWeek();

        return view('components.⚡dashboard.dashboard', [
            'todaysStandups' => Standup::query()
                ->with(['user', 'focusAreas', 'standupTasks.task'])
                ->whereDate('date', today())
                ->latest()
                ->get(),

            'teamUpdates' => Standup::query()
                ->with(['user', 'standupTasks.task'])
                ->whereDate('date', today())
                ->where('user_id', '!=', $user->id)
                ->latest()
                ->limit(6)
                ->get(),

            'recentPartnerNotifications' => PartnerNotification::query()
                ->with(['fromUser'])
                ->where('user_id', $user->id)
                ->latest()
                ->limit(6)
                ->get(),

            'upcomingEvents' => Event::query()
                ->with(['user', 'type'])
                ->where('starts_at', '>=', now())
                ->where('starts_at', '<=', now()->addDays(7))
                ->orderBy('starts_at')
                ->limit(5)
                ->get(),

            'weeklyStandupsCount' => Standup::query()
                ->whereBetween('date', [$weeklyStart->toDateString(), $weeklyEnd->toDateString()])
                ->count(),

            'weeklyEventsCount' => Event::query()
                ->whereBetween('starts_at', [$weeklyStart, $weeklyEnd])
                ->count(),

            'myStandup' => $user->todaysStandup(),
        ]);
    }
};
