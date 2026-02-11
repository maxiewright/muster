<?php

declare(strict_types=1);

namespace App\Enums;

enum PartnerStatus: string
{
    case None = 'none';
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';

    public function label(): string
    {
        return match($this) {
            self::None => 'No Partner',
            self::Pending => 'Pending Response',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::None => '👤',
            self::Pending => '⏳',
            self::Accepted => '🤝',
            self::Declined => '❌',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::None => 'text-gray-500',
            self::Pending => 'text-amber-500',
            self::Accepted => 'text-green-500',
            self::Declined => 'text-red-500',
        };
    }
}
