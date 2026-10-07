<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Mail\StaffInvitationMail;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SendStaffInvitationAction
{
    public function execute(User $actor, User $member): void
    {
        Gate::forUser($actor)->authorize('update', $member);

        if ($member->status?->isActive() && ! $member->hasPendingInvitation()) {
            throw ValidationException::withMessages(['member' => __('This account is already active.')]);
        }

        $token = Str::random(64);

        $member->forceFill([
            'invitation_token' => hash('sha256', $token),
            'invitation_sent_at' => now(),
        ])->save();

        Mail::to($member->email)->send(new StaffInvitationMail(
            $member,
            route('invitations.show', $token),
            $actor->name,
        ));

        activity('User')
            ->causedBy($actor)
            ->performedOn($member)
            ->withProperties(['action' => 'invitation_sent'])
            ->log('Staff invitation sent');
    }
}
