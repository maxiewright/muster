<?php

namespace App\Enums;

enum Role: string
{
    case Lead = 'lead';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Lead => 'Lead',
            self::Member => 'Member',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Lead => '👔',
            self::Member => '👤',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Lead => 'briefcase',
            self::Member => 'user',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Lead => 'bg-blue-200',
            self::Member => 'bg-green-200',
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::Lead => [
                'create_tasks',
                'assign_tasks',
                'delete_tasks',
                'manage_team',
                'view_all_stats',
            ],
            self::Member => [
                'create_tasks',
                'view_own_tasks',
            ],
        };
    }
}
