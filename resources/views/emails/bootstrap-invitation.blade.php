<x-mail::message>
# Complete Your Organization Setup

You have been selected as the first Organization Commander for a new {{ config('app.name') }} organization.

Use the link below to create the organization record and first unit in a single onboarding flow. This invitation expires on {{ optional($invitation->expires_at)->format('M j, Y g:i A') }}.

<x-mail::button :url="$setupUrl">
Complete Setup
</x-mail::button>

If the button does not work, use this link:

{{ $setupUrl }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
