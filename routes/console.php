<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('horizon:snapshot')->everyFiveMinutes();

Schedule::command('pulse:check')->everyFiveMinutes()->withoutOverlapping();

Schedule::command('queue:prune-batches --hours=48')->daily();
