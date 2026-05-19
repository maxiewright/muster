<?php

declare(strict_types=1);

namespace App\Enums;

enum ProgressTier: string
{
    case Foundation = 'foundation';
    case Building = 'building';
    case Developing = 'developing';
    case Steady = 'steady';
    case Capable = 'capable';
    case Proficient = 'proficient';
    case Advanced = 'advanced';
    case Strategic = 'strategic';
    case Expert = 'expert';
    case Principal = 'principal';
    case Vanguard = 'vanguard';

    public function label(): string
    {
        return match ($this) {
            self::Foundation => 'Foundation',
            self::Building => 'Building',
            self::Developing => 'Developing',
            self::Steady => 'Steady',
            self::Capable => 'Capable',
            self::Proficient => 'Proficient',
            self::Advanced => 'Advanced',
            self::Strategic => 'Strategic',
            self::Expert => 'Expert',
            self::Principal => 'Principal',
            self::Vanguard => 'Vanguard',
        };
    }

    public function minPoints(): int
    {
        return match ($this) {
            self::Foundation => 0,
            self::Building => 50,
            self::Developing => 150,
            self::Steady => 300,
            self::Capable => 500,
            self::Proficient => 800,
            self::Advanced => 1200,
            self::Strategic => 1800,
            self::Expert => 2500,
            self::Principal => 3500,
            self::Vanguard => 5000,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Foundation => '🌱',
            self::Building => '🧱',
            self::Developing => '🧭',
            self::Steady => '⚙️',
            self::Capable => '🛠️',
            self::Proficient => '📘',
            self::Advanced => '🚀',
            self::Strategic => '🗺️',
            self::Expert => '💡',
            self::Principal => '🏛️',
            self::Vanguard => '🌟',
        };
    }

    public function nextTier(): ?self
    {
        $cases = self::cases();
        $currentIndex = array_search($this, $cases, true);

        return $cases[$currentIndex + 1] ?? null;
    }

    public function progressToNext(int $points): int
    {
        $nextTier = $this->nextTier();

        if (! $nextTier instanceof ProgressTier) {
            return 100;
        }

        $currentMin = $this->minPoints();
        $nextMin = $nextTier->minPoints();
        $range = $nextMin - $currentMin;

        if ($range <= 0) {
            return 100;
        }

        return (int) min(100, max(0, (($points - $currentMin) / $range) * 100));
    }

    public static function fromPoints(int $points): self
    {
        $resolved = self::Foundation;

        foreach (self::cases() as $tier) {
            if ($points >= $tier->minPoints()) {
                $resolved = $tier;
            }
        }

        return $resolved;
    }
}
