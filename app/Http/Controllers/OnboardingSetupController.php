<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreInitialUserRequest;
use App\Models\Organization;
use App\Models\TeamInvitation;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnboardingSetupController extends Controller
{
    public function create(TeamInvitation $invitation): Factory|View|RedirectResponse
    {
        if (($redirect = $this->invalidInvitationRedirect($invitation)) instanceof RedirectResponse) {
            return $redirect;
        }

        return view('onboarding.setup', [
            'invitation' => $invitation,
        ]);
    }

    public function store(StoreInitialUserRequest $request, TeamInvitation $invitation): RedirectResponse
    {
        if (($redirect = $this->invalidInvitationRedirect($invitation)) instanceof RedirectResponse) {
            return $redirect;
        }

        if (User::query()->where('email', $invitation->email)->exists()) {
            return to_route('login')->withErrors([
                'email' => 'This setup invitation is already attached to an account.',
            ]);
        }

        $user = DB::transaction(function () use ($invitation, $request): User {
            $organization = Organization::query()->create([
                'name' => $request->string('organization_name')->value(),
                'slug' => Str::slug($request->string('organization_name')->value()),
            ]);

            $unit = Unit::query()->create([
                'organization_id' => $organization->id,
                'name' => $request->string('unit_name')->value(),
                'slug' => Str::slug($request->string('unit_name')->value()),
            ]);

            $user = User::query()->create([
                'name' => $request->string('name')->value(),
                'email' => $invitation->email,
                'organization_id' => $organization->id,
                'password' => $request->string('password')->value(),
                'role' => Role::Lead->value,
            ]);

            UnitMembership::query()->create([
                'user_id' => $user->id,
                'unit_id' => $unit->id,
                'role' => 'owner',
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();

            return $user;
        });

        $invitation->markAsAccepted();

        auth()->login($user, remember: true);
        $request->session()->put('active_unit_id', $user->activeUnitId());

        return to_route('dashboard');
    }

    private function invalidInvitationRedirect(TeamInvitation $invitation): ?RedirectResponse
    {
        if (! $invitation->isBootstrap()) {
            return to_route('login')->withErrors([
                'email' => 'This invitation must be completed through the team invitation flow.',
            ]);
        }

        if ($invitation->hasBeenAccepted()) {
            return to_route('login')->withErrors([
                'email' => 'This organization setup invitation was already used.',
            ]);
        }

        if ($invitation->hasExpired()) {
            return to_route('login')->withErrors([
                'email' => 'This organization setup invitation has expired.',
            ]);
        }

        return null;
    }
}
