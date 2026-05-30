<?php

declare(strict_types=1);

namespace App\Http;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        $socialiteUser = Socialite::driver($provider)
            ->user();

        $email = $socialiteUser->getEmail();

        if (empty($email)) {
            return to_route('login')->withErrors([
                'socialite' => 'Your '.$provider.' account does not provide an email address.',
            ]);
        }

        $user = User::query()
            ->where('oauth_provider', $provider)
            ->where('oauth_id', $socialiteUser->getId())
            ->first();

        if (! $user) {
            $user = User::query()->where('email', $email)->first();
        }

        if (! $user) {
            $invitation = TeamInvitation::query()
                ->team()
                ->pending()
                ->where('email', $email)
                ->latest()
                ->first();

            if (! $invitation || $invitation->hasExpired()) {
                return to_route('login')->withErrors([
                    'socialite' => 'No active invitation found for this email.',
                ]);
            }

            $user = User::query()->create([
                'name' => $socialiteUser->getName() ?? $socialiteUser->getNickname() ?? 'Operator',
                'email' => $email,
                'password' => Hash::make(str()->random(24)),
                'organization_id' => $invitation->organization_id ?? $invitation->inviter?->organization_id,
                'role' => $invitation->role,
                'oauth_provider' => $provider,
                'oauth_id' => $socialiteUser->getId(),
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();

            $defaultUnit = $invitation->unit ?? $invitation->inviter?->firstAvailableUnit();

            if ($defaultUnit !== null) {
                UnitMembership::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'unit_id' => $defaultUnit->id,
                    ],
                    [
                        'role' => $invitation->role === Role::Lead->value ? 'commander' : 'member',
                    ],
                );
            }

            $invitation->markAsAccepted();
        } elseif (! $user->oauth_provider || ! $user->oauth_id) {
            $user->forceFill([
                'oauth_provider' => $provider,
                'oauth_id' => $socialiteUser->getId(),
            ])->save();
        }

        auth()->login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }

    protected function validateProvider(string $provider): void
    {
        if (! in_array($provider, ['google', 'github'], true)) {
            abort(404);
        }
    }
}
