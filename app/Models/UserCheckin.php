<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCheckin extends Model
{
    protected $fillable = [
        'user_id',
        'on',
        'at',
        'from_ip',
    ];

    protected function casts(): array
    {
        return [
            'on' => 'date',
            'at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
