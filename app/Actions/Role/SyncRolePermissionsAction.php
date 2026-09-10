<?php

declare(strict_types=1);

namespace App\Actions\Role;

use App\Models\SystemComponent;
use App\Overrides\Spatie\Permission;
use App\Overrides\Spatie\Role;
use App\Utils\PermissionsUtil;
use Illuminate\Support\Collection;

final class SyncRolePermissionsAction
{
    /**
     * @param  array<int, int|string>|null  $permissionIds
     */
    public function __invoke(Role $role, ?int $objectId, ?array $permissionIds): Role
    {
        if ($objectId === null) {
            return $role;
        }

        $parentNode = SystemComponent::query()->find($objectId);

        if ($parentNode === null) {
            return $role;
        }

        /** @var Collection<int, int> $childNodes */
        $childNodes = SystemComponent::query()
            ->whereDescendantOf($parentNode)
            ->get()
            ->whereIn('comp_type', [3, 4])
            ->pluck('id');

        $objectsPermIds = Permission::query()
            ->whereIn('system_component_id', $childNodes)
            ->pluck('id');

        $role->revokePermissionTo($objectsPermIds);

        if ($permissionIds !== null && $permissionIds !== []) {
            $role->givePermissionTo($permissionIds);
        }

        PermissionsUtil::clearPermissionCash();

        return $role->fresh() ?? $role;
    }
}
