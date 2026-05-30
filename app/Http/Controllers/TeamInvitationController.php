<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\AcceptTeamInvitationRequest;
use App\Http\Requests\StoreTeamInvitationRequest;
use App\Mail\TeamInvitationMail;
use App\Models\TeamInvitation;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeamInvitationController extends Controller
{
    public function index(): Factory|View
    {
        $user = auth()->user();

        abort_unless($user?->organization_id !== null, 403);

        $availableUnits = $user->canManageOrganization()
            ? Unit::query()
                ->where('organization_id', $user->organization_id)
                ->orderBy('name')
                ->get()
            : $user->units()
                ->orderBy('units.name')
                ->get();

        $activeUnitId = $user->activeUnitId() ?? $availableUnits->first()?->id;

        return view('team.invitations.index', [
            'pendingInvitations' => TeamInvitation::query()
                ->team()
                ->inUnit($activeUnitId)
                ->with(['inviter', 'unit'])
                ->pending()
                ->latest()
                ->limit(20)
                ->get(),
            'acceptedInvitations' => TeamInvitation::query()
                ->team()
                ->inUnit($activeUnitId)
                ->with(['inviter', 'unit'])
                ->whereNotNull('accepted_at')
                ->latest('accepted_at')
                ->limit(10)
                ->get(),
            'availableUnits' => $availableUnits,
            'roles' => Role::cases(),
        ]);
    }

    public function store(StoreTeamInvitationRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->organization_id !== null, 403);

        $activeUnit = Unit::query()
            ->where('organization_id', $user->organization_id)
            ->findOrFail($request->integer('unit_id'));

        abort_unless($user->canInviteMembers($activeUnit), 403);

        $email = $request->string('email')->lower()->value();

        if (User::query()->where('email', $email)->exists()) {
            return back()->withErrors(['email' => 'This email already belongs to a team member.']);
        }

        TeamInvitation::query()
            ->pending()
            ->inUnit($activeUnit->id)
            ->where('email', $email)
            ->delete();

        $invitation = TeamInvitation::query()->create([
            'kind' => TeamInvitation::KIND_TEAM,
            'organization_id' => $user->organization_id,
            'unit_id' => $activeUnit->id,
            'invited_by_user_id' => $user->id,
            'email' => $email,
            'role' => $request->string('role')->value(),
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->queue(new TeamInvitationMail($invitation));

        return back()->with('status', 'Invitation sent successfully.');
    }

    public function showAcceptForm(TeamInvitation $invitation): Factory|View|RedirectResponse
    {
        if ($invitation->isBootstrap()) {
            return to_route('setup', $invitation);
        }

        if ($invitation->hasBeenAccepted()) {
            return to_route('login')->withErrors(['email' => 'This invitation was already used.']);
        }

        if ($invitation->hasExpired()) {
            return to_route('login')->withErrors(['email' => 'This invitation has expired.']);
        }

        return view('team.invitations.accept', [
            'invitation' => $invitation,
        ]);
    }

    public function accept(AcceptTeamInvitationRequest $request, TeamInvitation $invitation): RedirectResponse
    {
        if ($invitation->isBootstrap()) {
            return to_route('setup', $invitation);
        }

        if ($invitation->hasBeenAccepted()) {
            return to_route('login')->withErrors(['email' => 'This invitation was already used.']);
        }

        if ($invitation->hasExpired()) {
            return to_route('login')->withErrors(['email' => 'This invitation has expired.']);
        }

        $user = User::query()->firstOrCreate(
            ['email' => $invitation->email],
            [
                'name' => $request->string('name')->value(),
                'organization_id' => $invitation->organization_id ?? $invitation->inviter?->organization_id,
                'password' => $request->string('password')->value(),
                'role' => $invitation->role,
            ],
        );

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

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

        auth()->login($user, remember: true);

        return to_route('dashboard');
    }
}
