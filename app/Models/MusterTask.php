<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MusterTaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MusterTask extends Model
{
    use HasFactory;

    protected $table = 'muster_task';

    protected $fillable = [
        'muster_id',
        'task_id',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => MusterTaskStatus::class,
            'notes' => 'array',
        ];
    }

    public function muster(): BelongsTo
    {
        return $this->belongsTo(Muster::class, 'muster_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
