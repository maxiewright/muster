<?php

declare(strict_types=1);

namespace App\Enums;

enum ConfidenceLevel: string
{
    case Struggling = 'struggling';
    case Uncertain = 'uncertain';
    case OnTrack = 'on_track';
    case Confident = 'confident';
    case CrushingIt = 'crushing_it';

    public function label(): string
    {
        return match($this) {
            self::Struggling => 'Struggling',
            self::Uncertain => 'Uncertain',
            self::OnTrack => 'On Track',
            self::Confident => 'Confident',
            self::CrushingIt => 'Crushing It',
        };
    }

    public function emoji(): string
    {
        return match($this) {
            self::Struggling => '😰',
            self::Uncertain => '🤔',
            self::OnTrack => '👍',
            self::Confident => '💪',
            self::CrushingIt => '🔥',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Struggling => 'text-red-500',
            self::Uncertain => 'text-amber-500',
            self::OnTrack => 'text-blue-500',
            self::Confident => 'text-green-500',
            self::CrushingIt => 'text-purple-500',
        };
    }
}
