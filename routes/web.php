<?php

use App\Http\SocialiteController;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function (): Factory|\Illuminate\Contracts\View\View {
    return view('welcome');
})->name('home');

Route::get('/health', function (): JsonResponse {
    $checks = ['database' => false, 'cache' => false];
    try {
        DB::connection()->getPdo();
        $checks['database'] = true;
    } catch (Throwable) {
    }
    try {
        Cache::put('health_check', true, 5);
        $checks['cache'] = Cache::get('health_check') === true;
    } catch (Throwable) {
    }
    $ok = $checks['database'] && $checks['cache'];

    return response()->json(['status' => $ok ? 'ok' : 'degraded', 'checks' => $checks], $ok ? 200 : 503);
})->name('health');

Route::middleware(['auth', 'verified', 'throttle:app'])->group(function () {
    Route::livewire('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('achievements', 'gamification.gamification')->name('gamification');

    // Standup
    Route::livewire('standup', 'standup.standup-board')->name('standups');
    Route::livewire('standup/create', 'standup.standup-form')->name('standup.create');
    Route::livewire('standup/{standup}/edit', 'standup.standup-form')->name('standup.edit');

    // Calendar
    Route::livewire('calendar', 'calendar.calendar-view')->name('calendar');

    // Tasks
    Route::livewire('tasks', 'task.task-board')->name('tasks');

    // Training & Goals
    Route::prefix('training')->name('training.')->group(function () {
        Route::livewire('/', 'training.training-dashboard')->name('dashboard');
        Route::livewire('/goals/create', 'training.training-goal-form')->name('goals.create');
        Route::livewire('/goals/{goal:slug}/edit', 'training.training-goal-form')->name('goals.edit');
        Route::livewire('/goals/{goal:slug}', 'training.training-goal-show')->name('goals.show');
        Route::livewire('/goals/{goal:slug}/checkin', 'training.training-checkin-form')->name('goals.checkin');
    });

});

Route::middleware(['guest'])->group(function () {
    Route::get('auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
        ->name('socialite.redirect');

    Route::get('auth/{provider}/callback', [SocialiteController::class, 'callback'])
        ->name('socialite.callback');
});

require __DIR__.'/settings.php';
