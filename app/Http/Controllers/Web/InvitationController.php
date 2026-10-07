<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\User\AcceptStaffInvitationAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function show(string $token): View
    {
        $member = AcceptStaffInvitationAction::findInvitee($token);

        return view('auth.invitation', ['member' => $member, 'token' => $token]);
    }

    public function accept(Request $request, string $token, AcceptStaffInvitationAction $action): RedirectResponse
    {
        $member = AcceptStaffInvitationAction::findInvitee($token);
        abort_if($member === null, 404);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $action->execute($member, $validated['password']);
        Auth::login($member);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', __('Your account is active. Welcome!'));
    }
}
