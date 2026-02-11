<?php

namespace App\Events;

use App\Models\Standup;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StandupCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, InteractsWithSockets, SerializesModels;

    public function __construct(public Standup $standup)
    {
        $this->standup->load('user');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('muster'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'standup' => [
                'id' => $this->standup->id,
                'user' => [
                    'id' => $this->standup->user->id,
                    'name' => $this->standup->user->name,
                ],
                'today' => $this->standup->today,
                'yesterday' => $this->standup->yesterday,
                'blockers' => $this->standup->blockers,
                'mood' => $this->standup->mood,
                'focus_area' => $this->standup->focus_area,
                'created_at' => $this->standup->created_at->format('H:i'),
            ],
        ];
    }
}
