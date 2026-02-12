<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreInitialUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class OnboardingSetupController extends Controller
{
    public function create(): Factory|View|RedirectResponse
    {
        if (User::query()->where('role', Role::Lead->value)->exists()) {
            return redirect()->route('home');
        }

        return view('onboarding.setup');
    }

    public function store(StoreInitialUserRequest $request): RedirectResponse
    {
        if (User::query()->where('role', Role::Lead->value)->exists()) {
            return redirect()->route('home');
        }

        $user = User::query()->create([
            'name' => $request->string('name')->value(),
            'email' => $request->string('email')->lower()->value(),
            'password' => $request->string('password')->value(),
            'role' => Role::Lead->value,
            'email_verified_at' => now(),
        ]);

        auth()->login($user, remember: true);

        return redirect()->route('dashboard');
    }
}
