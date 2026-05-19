<?php

declare(strict_types=1);

namespace App\Enums;

enum MusterTaskStatus: string
{
    case Planned = 'planned';

    case Ongoing = 'ongoing';
    case CarriedOver = 'carried_over';
    case Completed = 'completed';

    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::Ongoing => 'Ongoing',
            self::CarriedOver => 'Carried Over',
            self::Completed => 'Completed',
            self::Blocked => 'Blocked',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Planned => '📝',
            self::Ongoing => '⏳',
            self::CarriedOver => '➡️',
            self::Completed => '✅',
            self::Blocked => '🚧',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Planned => 'clipboard',
            self::Ongoing => 'loading',
            self::CarriedOver => 'arrow-right',
            self::Completed => 'check-circle',
            self::Blocked => 'exclamation-triangle',
        };
    }
}
