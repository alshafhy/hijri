<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class UpdateStaffAccessAction
{
    public function setStatus(User $actor, User $member, UserStatus $status): User
    {
        $this->guard($actor, $member);

        if ($status->isActive() && $member->hasPendingInvitation()) {
            throw ValidationException::withMessages(['member' => __('The member must accept the invitation first.')]);
        }

        $member->forceFill(['status' => $status->value])->save();

        return $member;
    }

    public function setFinalApproval(User $actor, User $member, bool $granted): User
    {
        $this->guard($actor, $member);

        if (! $member->isStaffMember()) {
            throw ValidationException::withMessages(['member' => __('Final approval can only be delegated to coordinators and evaluators.')]);
        }

        $member->forceFill(['can_approve_final_request' => $granted])->save();

        return $member;
    }

    public function setMembershipNumber(User $actor, User $member, ?string $number): User
    {
        Gate::forUser($actor)->authorize('update', $member);

        $member->forceFill(['membership_number' => $number])->save();

        return $member;
    }

    private function guard(User $actor, User $member): void
    {
        Gate::forUser($actor)->authorize('update', $member);

        if ($actor->is($member)) {
            throw ValidationException::withMessages(['member' => __('You cannot change your own access.')]);
        }
    }
}
