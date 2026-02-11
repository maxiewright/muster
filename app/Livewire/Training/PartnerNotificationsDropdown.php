<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Models\PartnerNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PartnerNotificationsDropdown extends Component
{
    #[Computed]
    public function notifications()
    {
        return Auth::user()->partnerNotifications()
            ->with(['fromUser', 'goal'])
            ->latest()
            ->take(10)
            ->get();
    }

    #[Computed]
    public function unreadCount()
    {
        return Auth::user()->partnerNotifications()->whereNull('read_at')->count();
    }

    public function markAsRead(int $id): void
    {
        $notification = PartnerNotification::findOrFail($id);
        if ($notification->user_id === Auth::id()) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->partnerNotifications()->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.training.partner-notifications-dropdown');
    }
}
