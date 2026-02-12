<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #09090b; color: #e4e4e7; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #18181b; border: 1px solid #3f3f46; border-radius: 12px; padding: 24px;">
        <h1 style="margin-top: 0; color: #f4f4f5; font-size: 22px;">You have been invited to join {{ config('app.name') }}</h1>
        <p style="line-height: 1.6; color: #d4d4d8;">
            {{ $invitation->inviter?->name ?? 'A team lead' }} invited you as a {{ ucfirst($invitation->role) }}.
        </p>
        <p style="line-height: 1.6; color: #d4d4d8;">
            This invitation expires on {{ optional($invitation->expires_at)->format('M j, Y g:i A') }}.
        </p>
        <p style="margin: 24px 0;">
            <a href="{{ $acceptUrl }}" style="display:inline-block; background:#059669; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:8px; font-weight:600;">
                Accept Invitation
            </a>
        </p>
        <p style="font-size: 12px; color: #a1a1aa;">
            If the button does not work, use this link:<br>
            <a href="{{ $acceptUrl }}" style="color:#34d399;">{{ $acceptUrl }}</a>
        </p>
    </div>
</body>
</html>
