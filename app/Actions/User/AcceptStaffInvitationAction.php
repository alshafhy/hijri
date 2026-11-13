<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class AcceptStaffInvitationAction
{
    public static function findInvitee(string $token): ?User
    {
        $member = User::query()->where('invitation_token', hash('sha256', $token))->first();

        if ($member === null || $member->invitation_sent_at === null) {
            return null;
        }

        $expiresAt = $member->invitation_sent_at->copy()->addHours((int) config('users.invitation_ttl_hours'));

        return $expiresAt->isFuture() ? $member : null;
    }

    public function execute(User $member, string $password): User
    {
        $member->forceFill([
            'password' => Hash::make($password),
            'status' => UserStatus::Active->value,
            'pass_need_to_be_changed' => 0,
            'email_verified_at' => now(),
            'invitation_token' => null,
            'invitation_sent_at' => null,
        ])->save();

        activity('User')
            ->causedBy($member)
            ->performedOn($member)
            ->withProperties(['action' => 'invitation_accepted'])
            ->log('Staff invitation accepted');

        return $member;
    }
}
