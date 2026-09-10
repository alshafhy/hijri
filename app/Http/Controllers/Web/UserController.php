<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\AppBaseController;
use App\Actions\User\ChangePasswordAction;
use App\Actions\User\CreateUserAction;
use App\Actions\User\DeleteUserAction;
use App\Actions\User\UpdateUserAction;
use App\DataTables\UserDataTable;
use App\DTOs\User\ChangePasswordData;
use App\DTOs\User\CreateUserData;
use App\DTOs\User\UpdateUserData;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Branch;
use App\Models\User;
use App\Overrides\Spatie\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

class UserController extends AppBaseController
{
    public function index(UserDataTable $userDataTable)
    {
        $this->authorize('viewAny', User::class);

        return $userDataTable->render('users.index');
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        $branches = Branch::query()->pluck('name', 'id')->prepend(__('messages.select'), '');

        return view('users.create', compact('branches'));
    }

    public function store(StoreUserRequest $request, CreateUserAction $action): RedirectResponse
    {
        $action(CreateUserData::fromRequest($request));

        Flash::success(__('messages.saved', ['model' => __('models/users.singular')]));

        return redirect(route('dashboard.users.index'));
    }

    public function show(User $user): View|RedirectResponse
    {
        $this->authorize('view', $user);

        return view('users.show')->with('user', $user);
    }

    public function edit(User $user): View|RedirectResponse
    {
        $this->authorize('update', $user);

        $roles_list = Role::query()->pluck('ar_name', 'id');

        $userRoles = $user->roles()
            ->select('id', 'ar_name')
            ->get();

        return view('users.edit', compact('user', 'roles_list', 'userRoles'));
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UpdateUserAction $action
    ): RedirectResponse {
        $action($user, UpdateUserData::fromRequest($request));

        Flash::success(__('messages.updated', ['model' => __('models/users.singular')]));

        return redirect(route('dashboard.users.index'));
    }

    public function destroy(User $user, DeleteUserAction $action): RedirectResponse
    {
        $this->authorize('delete', $user);

        $action($user);

        Flash::success(__('messages.deleted', ['model' => __('models/users.singular')]));

        return redirect(route('dashboard.users.index'));
    }

    public function changePassword(): View
    {
        return view('users.change-password');
    }

    public function updatePassword(
        ChangePasswordRequest $request,
        ChangePasswordAction $action
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $action($user, ChangePasswordData::fromRequest($request));

        return back()->with('status', __('auth.password_changed'));
    }
}
