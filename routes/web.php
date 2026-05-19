<?php

use App\Http\Controllers\ActiveUnitController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\OnboardingSetupController;
use App\Http\Controllers\SystemSetupController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\UnitController;
use App\Http\Middleware\EnsureActiveUnitContext;
use App\Http\SocialiteController;
use App\Livewire\Muster\MusterBoard;
use App\Livewire\Muster\MusterForm;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function (): Factory|View|RedirectResponse {
    if (! User::query()->where('is_platform_admin', true)->exists()) {
        return to_route('system.setup');
    }

    if (Auth::check()) {
        if (Auth::user()?->isPlatformAdmin()) {
            return to_route('filament.admin.pages.dashboard');
        }

        return to_route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('/system/setup', [SystemSetupController::class, 'create'])->name('system.setup');
Route::post('/system/setup', [SystemSetupController::class, 'store'])->name('system.setup.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/setup/{invitation:token}', [OnboardingSetupController::class, 'create'])->name('setup');
    Route::post('/setup/{invitation:token}', [OnboardingSetupController::class, 'store'])->name('setup.store');
});

Route::middleware(['guest', 'throttle:invites'])->group(function (): void {
    Route::get('/invites/{invitation:token}', [TeamInvitationController::class, 'showAcceptForm'])->name('invites.accept');
    Route::post('/invites/{invitation:token}', [TeamInvitationController::class, 'accept'])->name('invites.accept.store');
});

Route::middleware('throttle:health')->get('/health', function (): JsonResponse {
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

Route::middleware(['auth', 'verified', 'throttle:app', EnsureActiveUnitContext::class])->group(function (): void {
    Route::post('units/active', ActiveUnitController::class)->name('units.active');
    Route::livewire('dashboard', 'dashboard')->name('dashboard');
    Route::get('missions', [MissionController::class, 'index'])->name('missions.index');
    Route::post('missions', [MissionController::class, 'store'])->name('missions.store');
    Route::prefix('team')->name('team.')->group(function (): void {
        Route::get('units', [UnitController::class, 'index'])->name('units.index');
        Route::post('units', [UnitController::class, 'store'])->name('units.store');
        Route::get('invitations', [TeamInvitationController::class, 'index'])->name('invitations');
        Route::post('invitations', [TeamInvitationController::class, 'store'])->name('invitations.store');
    });
    Route::livewire('achievements', 'gamification.gamification')->name('gamification');

    // Muster
    Route::livewire('musters', MusterBoard::class)->name('musters');
    Route::middleware('throttle:muster-submit')->group(function (): void {
        Route::livewire('musters/create', MusterForm::class)->name('muster.create');
    });
    Route::livewire('musters/{muster}/edit', MusterForm::class)->name('muster.edit');

    // Calendar
    Route::livewire('calendar', 'calendar.calendar-view')->name('calendar');

    // Tasks
    // Task create/update POST actions are Livewire requests and continue to pass through the main throttle:app middleware.
    Route::livewire('tasks', 'task.task-board')->name('tasks');

    // Training & Goals
    Route::prefix('training')->name('training.')->group(function (): void {
        Route::livewire('/', 'training.training-dashboard')->name('dashboard');
        Route::livewire('/assignments', 'training.training-assignment-manager')->name('assignments');
        Route::livewire('/goals/create', 'training.training-goal-form')->name('goals.create');
        Route::livewire('/goals/{goal:slug}/edit', 'training.training-goal-form')->name('goals.edit');
        Route::livewire('/goals/{goal:slug}', 'training.training-goal-show')->name('goals.show');
        Route::livewire('/goals/{goal:slug}/checkin', 'training.training-checkin-form')->name('goals.checkin');
    });

});

Route::middleware(['guest', 'throttle:socialite'])->group(function (): void {
    Route::get('auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
        ->name('socialite.redirect');

    Route::get('auth/{provider}/callback', [SocialiteController::class, 'callback'])
        ->name('socialite.callback');
});

require __DIR__.'/settings.php';
