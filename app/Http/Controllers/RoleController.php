<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Role\SyncRolePermissionsAction;
use App\DataTables\RoleDataTable;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\SystemComponent;
use App\Overrides\Spatie\Permission;
use App\Overrides\Spatie\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

class RoleController extends AppBaseController
{
    public function index(RoleDataTable $roleDataTable)
    {
        $this->authorize('viewAny', Role::class);

        return $roleDataTable->render('roles.index');
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('roles.create');
    }

    public function store(CreateRoleRequest $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $role = Role::query()->create($request->all());

        Flash::success(__('messages.saved', ['model' => __('models/roles.singular')]));

        return redirect(route('dashboard.roles.index'));
    }

    public function show(Role $role): View|RedirectResponse
    {
        $this->authorize('view', $role);

        return view('roles.show')->with('role', $role);
    }

    public function edit(Role $role): View|RedirectResponse
    {
        $this->authorize('update', $role);

        $sysScreens = SystemComponent::query()->where('comp_type', 1)->get();

        return view('roles.edit')
            ->with('role', $role)
            ->with('sysScreens', $sysScreens);
    }

    public function update(
        UpdateRoleRequest $request,
        Role $role,
        SyncRolePermissionsAction $syncRolePermissionsAction
    ): RedirectResponse {
        $this->authorize('update', $role);

        $role->fill($request->all());
        $role->save();

        if ($request->has('objectId')) {
            $permissionIds = $request->input('permission');
            $syncRolePermissionsAction(
                $role,
                $request->filled('objectId') ? (int) $request->input('objectId') : null,
                is_array($permissionIds) ? $permissionIds : null,
            );
        }

        Flash::success(__('messages.updated', ['model' => __('models/roles.singular')]));

        return redirect(route('dashboard.roles.index'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $role->delete();

        Flash::success(__('messages.deleted', ['model' => __('models/roles.singular')]));

        return redirect(route('dashboard.roles.index'));
    }

    public function getPermissionsView(int $id, ?int $objectId = null): View
    {
        $role = Role::query()->findOrFail($id);
        $this->authorize('update', $role);

        $node = SystemComponent::query()->find($objectId);

        $nodes = $node === null
            ? collect()
            : SystemComponent::query()
                ->whereDescendantOf($node)
                ->get()
                ->whereIn('comp_type', [3, 4]);

        $permission = Permission::query()->get();

        $rolePermissions = DB::table('role_has_permissions')
            ->where('role_has_permissions.role_id', $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();

        return view('roles._permissions', compact('permission', 'nodes', 'role', 'rolePermissions', 'objectId'));
    }
}
