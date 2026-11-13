<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\User\SubmitSupportRequestAction;
use App\Actions\User\UpdateOwnProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateOwnProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'supportRequests' => $request->user()->supportRequests()->latest()->limit(10)->get(),
            'tab' => $request->query('tab', 'info'),
        ]);
    }

    public function update(UpdateOwnProfileRequest $request, UpdateOwnProfileAction $action): RedirectResponse
    {
        $action->update($request->user(), $request->validated());

        return redirect()->route('dashboard.profile.edit')->with('success', __('Profile updated.'));
    }

    public function uploadImage(Request $request, string $type, UpdateOwnProfileAction $action): RedirectResponse
    {
        abort_unless(array_key_exists($type, UpdateOwnProfileAction::IMAGE_TYPES), 404);

        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $action->uploadImage($request->user(), $type, $validated['image']);

        return redirect()->route('dashboard.profile.edit', ['tab' => $type])->with('success', __('Image uploaded.'));
    }

    public function image(Request $request, User $user, string $type): StreamedResponse
    {
        $column = UpdateOwnProfileAction::IMAGE_TYPES[$type] ?? null;
        abort_if($column === null, 404);
        abort_unless($request->user()->is($user) || $request->user()->can('view', $user), 403);

        $path = $user->{$column};
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    public function support(Request $request, SubmitSupportRequestAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'tab' => ['nullable', Rule::in(['help'])],
        ]);

        $action->execute($request->user(), $validated);

        return redirect()->route('dashboard.profile.edit', ['tab' => 'help'])->with('success', __('Your support request was sent.'));
    }
}
