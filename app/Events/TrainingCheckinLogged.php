<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\TrainingCheckin;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TrainingCheckinLogged implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TrainingCheckin $checkin,
        public User $recipient
    ) {
        $this->checkin->loadMissing(['user', 'goal']);
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.User.{$this->recipient->id}"),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'title' => "{$this->checkin->user->name} logged training progress",
            'message' => $this->checkin->progress_update,
            'from_user_name' => $this->checkin->user->name,
            'goal_slug' => $this->checkin->goal?->slug,
            'goal_title' => $this->checkin->goal?->title,
        ];
    }
}
