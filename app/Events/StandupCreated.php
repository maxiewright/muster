<?php

namespace App\Events;

use App\Models\Standup;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StandupCreated implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, InteractsWithSockets, SerializesModels;

    public function __construct(public Standup $standup)
    {
        $this->standup->loadMissing(['user', 'tasks', 'focusAreas']);
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
                'date' => $this->standup->date?->toDateString(),
                'blockers' => $this->standup->blockers,
                'mood' => $this->standup->mood,
                'focus_areas' => $this->standup->focusAreas->pluck('name')->all(),
                'tasks' => $this->standup->tasks->map(fn ($task): array => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->pivot?->status,
                ])->all(),
                'created_at' => $this->standup->created_at->format('H:i'),
            ],
        ];
    }
}
