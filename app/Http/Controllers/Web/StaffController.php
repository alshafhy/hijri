<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\User\InviteStaffMemberAction;
use App\Actions\User\SendStaffInvitationAction;
use App\Actions\User\UpdateStaffAccessAction;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\InviteStaffMemberRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function invite(InviteStaffMemberRequest $request, InviteStaffMemberAction $action): RedirectResponse
    {
        $member = $action->execute($request->user(), $request->validated());

        return redirect()
            ->route('dashboard.users.show', $member)
            ->with('success', __('Invitation sent to :email.', ['email' => $member->email]));
    }

    public function resendInvitation(Request $request, User $user, SendStaffInvitationAction $action): RedirectResponse
    {
        $action->execute($request->user(), $user);

        return back()->with('success', __('Invitation sent to :email.', ['email' => $user->email]));
    }

    public function updateStatus(Request $request, User $user, UpdateStaffAccessAction $action): RedirectResponse
    {
        $validated = $request->validate(['active' => ['required', 'boolean']]);

        $action->setStatus($request->user(), $user, $validated['active'] ? UserStatus::Active : UserStatus::Inactive);

        return back()->with('success', __('User status updated.'));
    }

    public function updateFinalApproval(Request $request, User $user, UpdateStaffAccessAction $action): RedirectResponse
    {
        $validated = $request->validate(['granted' => ['required', 'boolean']]);

        $action->setFinalApproval($request->user(), $user, (bool) $validated['granted']);

        return back()->with('success', $validated['granted']
            ? __('Final approval delegated.')
            : __('Final approval delegation revoked.'));
    }

    public function updateMembership(Request $request, User $user, UpdateStaffAccessAction $action): RedirectResponse
    {
        $validated = $request->validate(['membership_number' => ['nullable', 'string', 'max:100']]);

        $action->setMembershipNumber($request->user(), $user, $validated['membership_number'] ?? null);

        return back()->with('success', __('Membership number updated.'));
    }
}
