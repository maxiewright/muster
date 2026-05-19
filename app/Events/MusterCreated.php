<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Muster;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MusterCreated implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Muster $muster)
    {
        $this->muster->loadMissing(['user', 'tasks', 'focusAreas']);
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('muster'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'muster' => [
                'id' => $this->muster->id,
                'user' => [
                    'id' => $this->muster->user->id,
                    'name' => $this->muster->user->name,
                ],
                'date' => $this->muster->date?->toDateString(),
                'blockers' => $this->muster->blockers,
                'mood' => $this->muster->mood,
                'focus_areas' => $this->muster->focusAreas->pluck('name')->all(),
                'tasks' => $this->muster->tasks->map(fn ($task): array => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->pivot?->status,
                ])->all(),
                'created_at' => $this->muster->created_at->format('H:i'),
            ],
        ];
    }
}
