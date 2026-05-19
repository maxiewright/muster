<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Mission;
use App\Models\Organization;
use App\Models\Task;
use App\Models\TeamInvitation;
use App\Models\TrainingGoal;
use App\Models\Unit;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Organizations', (string) Organization::query()->count())
                ->description('Provisioned commands'),
            Stat::make('Units', (string) Unit::query()->count())
                ->description('Active organizational units'),
            Stat::make('Users', (string) User::query()->count())
                ->description('Accounts across the platform'),
            Stat::make('Missions', (string) Mission::query()->count())
                ->description('Recorded missions'),
            Stat::make('Actions', (string) Task::query()->count())
                ->description('Tracked actions'),
            Stat::make('Pending Invites', (string) TeamInvitation::query()->bootstrap()->pending()->count())
                ->description('Awaiting commander setup'),
            Stat::make('Training Assignments', (string) TrainingGoal::query()->where('is_unit_directed', true)->count())
                ->description('Commander-directed training'),
        ];
    }
}
