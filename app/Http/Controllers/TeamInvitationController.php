<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\AcceptTeamInvitationRequest;
use App\Http\Requests\StoreTeamInvitationRequest;
use App\Mail\TeamInvitationMail;
use App\Models\TeamInvitation;
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
        abort_unless(auth()->user()?->isLead(), 403);

        return view('team.invitations.index', [
            'pendingInvitations' => TeamInvitation::query()
                ->with('inviter')
                ->pending()
                ->latest()
                ->limit(20)
                ->get(),
            'acceptedInvitations' => TeamInvitation::query()
                ->with('inviter')
                ->whereNotNull('accepted_at')
                ->latest('accepted_at')
                ->limit(10)
                ->get(),
            'roles' => Role::cases(),
        ]);
    }

    public function store(StoreTeamInvitationRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isLead(), 403);

        $email = $request->string('email')->lower()->value();

        if (User::query()->where('email', $email)->exists()) {
            return back()->withErrors(['email' => 'This email already belongs to a team member.']);
        }

        TeamInvitation::query()
            ->pending()
            ->where('email', $email)
            ->delete();

        $invitation = TeamInvitation::query()->create([
            'invited_by_user_id' => auth()->id(),
            'email' => $email,
            'role' => $request->string('role')->value(),
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->send(new TeamInvitationMail($invitation));

        return back()->with('status', 'Invitation sent successfully.');
    }

    public function showAcceptForm(TeamInvitation $invitation): Factory|View|RedirectResponse
    {
        if ($invitation->hasBeenAccepted()) {
            return redirect()->route('login')->withErrors(['email' => 'This invitation was already used.']);
        }

        if ($invitation->hasExpired()) {
            return redirect()->route('login')->withErrors(['email' => 'This invitation has expired.']);
        }

        return view('team.invitations.accept', [
            'invitation' => $invitation,
        ]);
    }

    public function accept(AcceptTeamInvitationRequest $request, TeamInvitation $invitation): RedirectResponse
    {
        if ($invitation->hasBeenAccepted()) {
            return redirect()->route('login')->withErrors(['email' => 'This invitation was already used.']);
        }

        if ($invitation->hasExpired()) {
            return redirect()->route('login')->withErrors(['email' => 'This invitation has expired.']);
        }

        $user = User::query()->firstOrCreate(
            ['email' => $invitation->email],
            [
                'name' => $request->string('name')->value(),
                'password' => $request->string('password')->value(),
                'role' => $invitation->role,
                'email_verified_at' => now(),
            ],
        );

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $invitation->markAsAccepted();

        auth()->login($user, remember: true);

        return redirect()->route('dashboard');
    }
}
