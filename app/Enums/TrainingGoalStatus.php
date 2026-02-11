<?php

declare(strict_types=1);

namespace App\Enums;

enum TrainingGoalStatus: string
{
    case Draft = 'draft';
    case PendingPartner = 'pending_partner';
    case Active = 'active';
    case Completed = 'completed';
    case Verified = 'verified';
    case Abandoned = 'abandoned';
    case Paused = 'paused';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Draft',
            self::PendingPartner => 'Awaiting Partner',
            self::Active => 'In Progress',
            self::Completed => 'Completed',
            self::Verified => 'Verified ✓',
            self::Abandoned => 'Abandoned',
            self::Paused => 'Paused',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Draft => '📝',
            self::PendingPartner => '⏳',
            self::Active => '🎯',
            self::Completed => '✅',
            self::Verified => '🏅',
            self::Abandoned => '🚫',
            self::Paused => '⏸️',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            self::PendingPartner => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
            self::Active => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
            self::Completed => 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300',
            self::Verified => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
            self::Abandoned => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300',
            self::Paused => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
        };
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::Draft, self::Active, self::Paused]);
    }

    public function canAddCheckins(): bool
    {
        return $this === self::Active;
    }

    public function canComplete(): bool
    {
        return $this === self::Active;
    }
}
