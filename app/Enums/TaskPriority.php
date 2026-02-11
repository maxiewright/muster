<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => 'green',
            self::Medium => 'blue',
            self::High => 'orange',
            self::Urgent => 'red',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Low => '🟢',
            self::Medium => '🔵',
            self::High => '🟠',
            self::Urgent => '🔴',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Low => 'arrow-down',
            self::Medium => 'bars-2',
            self::High => 'arrow-up-circle',
            self::Urgent => 'exclamation-circle',
        };
    }
}
