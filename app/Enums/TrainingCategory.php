<?php

declare(strict_types=1);

namespace App\Enums;

enum TrainingCategory: string
{
    case Technical = 'technical';
    case Framework = 'framework';
    case Language = 'language';
    case SoftSkill = 'soft_skill';
    case Certification = 'certification';
    case Project = 'project';
    case Course = 'course';
    case Book = 'book';

    public function label(): string
    {
        return match($this) {
            self::Technical => 'Technical Skill',
            self::Framework => 'Framework/Library',
            self::Language => 'Programming Language',
            self::SoftSkill => 'Soft Skill',
            self::Certification => 'Certification',
            self::Project => 'Project-Based',
            self::Course => 'Online Course',
            self::Book => 'Book/Reading',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Technical => '⚙️',
            self::Framework => '🏗️',
            self::Language => '💻',
            self::SoftSkill => '🗣️',
            self::Certification => '📜',
            self::Project => '🚀',
            self::Course => '🎓',
            self::Book => '📚',
        };
    }
}
