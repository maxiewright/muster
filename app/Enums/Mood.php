<?php

namespace App\Enums;

enum Mood: string
{
    case Firing = 'firing';
    case Steady = 'steady';
    case Strong = 'strong';
    case Struggling = 'struggling';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Firing => 'Firing',
            self::Steady => 'Steady',
            self::Strong => 'Strong',
            self::Struggling => 'Struggling',
            self::Blocked => 'Blocked',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Firing => '🔥',
            self::Steady => '👍',
            self::Strong => '💪',
            self::Struggling => '😓',
            self::Blocked => '🚧',
        };
    }
}
