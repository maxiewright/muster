<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Event;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EventCompleted implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, InteractsWithSockets, SerializesModels;

    public Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->event->loadMissing('type');
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.User.{$this->event->user_id}"),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'event' => [
                'id' => $this->event->id,
                'title' => $this->event->title,
                'completed_at' => now()->toIso8601String(),
                'color' => $this->event->color ?? $this->event->type?->color,
            ],
        ];
    }
}
