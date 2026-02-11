<?php

namespace App\Enums;

enum EventType: string
{
    case Huddle = 'huddle';
    case Training = 'training';
    case Pairing = 'pairing';
    case Review = 'review';

    public function label(): string
    {
        return match ($this) {
            self::Huddle => '🤝 Huddle',
            self::Training => '📚 Training',
            self::Pairing => '👥 Pair Programming',
            self::Review => '🔍 Code Review',
        };
    }
}
