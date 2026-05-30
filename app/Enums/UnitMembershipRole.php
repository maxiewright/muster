<?php

declare(strict_types=1);

namespace App\Enums;

enum UnitMembershipRole: string
{
    case Commander = 'commander';
    case Lead = 'lead';
    case Advisor = 'advisor';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Commander => 'Commander',
            self::Lead => 'Lead',
            self::Advisor => 'Advisor',
            self::Member => 'Member',
        };
    }
}
