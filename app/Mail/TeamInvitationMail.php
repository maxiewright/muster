<?php

namespace App\Mail;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TeamInvitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been invited to join '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.team-invitation',
            with: [
                'invitation' => $this->invitation,
                'acceptUrl' => route('invites.accept', $this->invitation),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
