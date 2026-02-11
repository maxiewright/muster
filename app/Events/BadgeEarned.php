<?php

namespace App\Events;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BadgeEarned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Badge $badge
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('muster'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'badge' => [
                'name' => $this->badge->name,
                'icon' => $this->badge->icon,
                'description' => $this->badge->description,
            ],
        ];
    }
}
