<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UnitMembershipRole;
use Database\Factories\UnitMembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitMembership extends Model
{
    /** @use HasFactory<UnitMembershipFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'unit_id',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'role' => UnitMembershipRole::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
