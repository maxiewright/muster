<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreSystemSetupRequest;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SystemSetupController extends Controller
{
    public function create(): Factory|View|RedirectResponse
    {
        if ($this->platformIsConfigured()) {
            return auth()->check() && auth()->user()?->isPlatformAdmin()
                ? to_route('filament.admin.pages.dashboard')
                : to_route('login');
        }

        return view('system.setup');
    }

    public function store(StoreSystemSetupRequest $request): RedirectResponse
    {
        if ($this->platformIsConfigured()) {
            return to_route('login');
        }

        $user = User::query()->create([
            'name' => $request->string('name')->value(),
            'email' => $request->string('email')->lower()->value(),
            'password' => $request->string('password')->value(),
            'role' => Role::Member->value,
            'is_platform_admin' => true,
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        auth()->login($user, remember: true);

        return to_route('filament.admin.pages.dashboard');
    }

    private function platformIsConfigured(): bool
    {
        return User::query()->where('is_platform_admin', true)->exists();
    }
}
