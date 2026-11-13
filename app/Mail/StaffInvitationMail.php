<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public User $member,
        public string $activationUrl,
        public string $invitedBy,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('You have been invited to :app', ['app' => config('app.name')]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.staff-invitation');
    }
}
