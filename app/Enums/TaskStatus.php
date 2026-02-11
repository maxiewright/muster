<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Backlog = 'backlog';
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Completed = 'completed';
    case Blocked = 'blocked';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Backlog => 'Backlog',
            self::Todo => 'To Do',
            self::InProgress => 'In Progress',
            self::Review => 'In Review',
            self::Completed => 'Completed',
            self::Blocked => 'Blocked',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Backlog => 'bg-gray-200',
            self::Todo => 'bg-blue-200',
            self::InProgress => 'bg-yellow-200',
            self::Review => 'bg-purple-200',
            self::Completed => 'bg-green-200',
            self::Blocked => 'bg-red-200',
            self::Cancelled => 'bg-black text-white',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Backlog => 'clipboard',
            self::Todo => 'list-bullet',
            self::InProgress => 'arrow-path',
            self::Review => 'magnifying-glass',
            self::Completed => 'check-circle',
            self::Blocked => 'exclamation-triangle',
            self::Cancelled => 'x-circle',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Backlog => '📋',
            self::Todo => '📝',
            self::InProgress => '⏳',
            self::Review => '🔍',
            self::Completed => '✅',
            self::Blocked => '🚧',
            self::Cancelled => '❌',
        };
    }
}
