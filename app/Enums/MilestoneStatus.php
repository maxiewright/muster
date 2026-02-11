<?php

declare(strict_types=1);

namespace App\Enums;

enum MilestoneStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Verified = 'verified';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Not Started',
            self::InProgress => 'In Progress',
            self::Completed => 'Awaiting Verification',
            self::Verified => 'Verified',
            self::Skipped => 'Skipped',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Pending => '○',
            self::InProgress => '◐',
            self::Completed => '●',
            self::Verified => '✓',
            self::Skipped => '⊘',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-800',
            self::InProgress => 'border-blue-500 bg-blue-100 dark:bg-blue-900/30',
            self::Completed => 'border-green-500 bg-green-100 dark:bg-green-900/30',
            self::Verified => 'border-purple-500 bg-purple-100 dark:bg-purple-900/30',
            self::Skipped => 'border-gray-300 bg-gray-100 dark:bg-gray-700',
        };
    }
}
