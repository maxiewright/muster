<?php

namespace App\Models;

use App\Enums\StandupTaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandUpTask extends Model
{
    use HasFactory;

    protected $table = 'standup_task';

    protected $fillable = [
        'standup_id',
        'task_id',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => StandupTaskStatus::class,
            'notes' => 'array',
        ];
    }

    public function standup(): BelongsTo
    {
        return $this->belongsTo(Standup::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
