<?php

use App\Enums\PartnerStatus;
use App\Models\Event;
use App\Models\Muster;
use App\Models\PartnerNotification;
use App\Models\Task;
use App\Services\TrainingGamificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public function acceptPartnerRequest(int $notificationId): void
    {
        $notification = PartnerNotification::query()
            ->with('goal')
            ->where('id', $notificationId)
            ->where('user_id', Auth::id())
            ->inUnit(Auth::user()->activeUnitId())
            ->where('type', 'partner_request')
            ->first();

        if (! $notification || ! $notification->goal) {
            return;
        }

        $goal = $notification->goal;
        if (! $goal->belongsToUserContext(Auth::user())
            || $goal->accountability_partner_id !== Auth::id()
            || $goal->partner_status !== PartnerStatus::Pending) {
            return;
        }

        $goal->update(['partner_status' => PartnerStatus::Accepted]);
        resolve(TrainingGamificationService::class)->onGoalActivated($goal->fresh());

        $notification->update([
            'read_at' => now(),
            'actioned_at' => now(),
        ]);
    }

    public function declinePartnerRequest(int $notificationId): void
    {
        $notification = PartnerNotification::query()
            ->with('goal')
            ->where('id', $notificationId)
            ->where('user_id', Auth::id())
            ->inUnit(Auth::user()->activeUnitId())
            ->where('type', 'partner_request')
            ->first();

        if (! $notification || ! $notification->goal) {
            return;
        }

        $goal = $notification->goal;
        if (! $goal->belongsToUserContext(Auth::user())
            || $goal->accountability_partner_id !== Auth::id()
            || $goal->partner_status !== PartnerStatus::Pending) {
            return;
        }

        $goal->update([
            'partner_status' => PartnerStatus::Declined,
            'partner_decline_reason' => 'Declined from dashboard notification',
        ]);

        $notification->update([
            'read_at' => now(),
            'actioned_at' => now(),
        ]);
    }

    public function render(): View
    {
        $user = auth()->user();
        $activeUnitId = $user?->activeUnitId();
        $weeklyStart = now()->startOfWeek();
        $weeklyEnd = now()->endOfWeek();

        return view('components.⚡dashboard.dashboard', [
            'todaysMusters' => Muster::query()
                ->with(['user', 'focusAreas', 'musterTasks.task.assignee'])
                ->inUnit($activeUnitId)
                ->whereDate('date', today())
                ->latest()
                ->get(),

            'teamUpdates' => Muster::query()
                ->with(['user', 'musterTasks.task.assignee'])
                ->inUnit($activeUnitId)
                ->whereDate('date', today())
                ->where('user_id', '!=', $user->id)
                ->latest()
                ->limit(6)
                ->get(),

            'recentPartnerNotifications' => PartnerNotification::query()
                ->with(['fromUser', 'goal'])
                ->where('user_id', $user->id)
                ->inUnit($activeUnitId)
                ->latest()
                ->limit(6)
                ->get(),

            'activeTasks' => Task::query()
                ->with(['assignee', 'creator'])
                ->inUnit($activeUnitId)
                ->where(function ($q) use ($user): void {
                    $q->where('assigned_to', $user->id)
                        ->orWhere('created_by', $user->id);
                })
                ->active()
                ->rootTasks()
                ->latest()
                ->limit(8)
                ->get(),

            'upcomingEvents' => Event::query()
                ->with(['user', 'type'])
                ->inUnit($activeUnitId)
                ->where('starts_at', '>=', now())
                ->where('starts_at', '<=', now()->addDays(7))
                ->oldest('starts_at')
                ->limit(5)
                ->get(),

            'weeklyMustersCount' => Muster::query()
                ->inUnit($activeUnitId)
                ->whereBetween('date', [$weeklyStart->toDateString(), $weeklyEnd->toDateString()])
                ->count(),

            'weeklyEventsCount' => Event::query()
                ->inUnit($activeUnitId)
                ->whereBetween('starts_at', [$weeklyStart, $weeklyEnd])
                ->count(),

            'myMuster' => $user->todaysMuster(),
        ]);
    }
};
