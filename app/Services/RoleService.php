<?php

namespace App\Services;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Class RoleService
 *
 * Service for managing roles in the system
 *
 * @package App\Services
 */
class RoleService
{
    /**
     * Get all roles with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllRoles(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Role::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get a role by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Role|null
     */
    public function getRoleById(int $id, array $relations = []): ?Role
    {
        $query = Role::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Get a role by name.
     *
     * @param string $name
     * @param array $relations
     * @return Role|null
     */
    public function getRoleByName(string $name, array $relations = []): ?Role
    {
        $query = Role::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->where('name', $name)->first();
    }

    /**
     * Create a new role
     *
     * @param array $data
     * @return Role
     */
    public function createRole(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'api',
        ]);

        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $permissions = Permission::query()->whereIn('name', $data['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        return $role->load('permissions');
    }

    /**
     * Update an existing role
     *
     * @param int $id
     * @param array $data
     * @return Role|null
     */
    public function updateRole(int $id, array $data): ?Role
    {
        $role = $this->getRoleById($id);

        if (!$role) {
            return null;
        }

        $role->update([
            'name' => $data['name'] ?? $role->name,
            'guard_name' => $data['guard_name'] ?? $role->guard_name,
        ]);

        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $permissions = Permission::query()->whereIn('name', $data['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        return $role->fresh('permissions');
    }

    /**
     * Delete a role
     *
     * @param int $id
     * @return bool
     */
    public function deleteRole(int $id): bool
    {
        $role = $this->getRoleById($id);

        if (!$role) {
            return false;
        }

        // Don't allow deletion of default roles
        if (in_array($role->name, RoleEnum::values())) {
            return false;
        }

        return $role->delete();
    }

    /**
     * Assign permissions to a role
     *
     * @param int $roleId
     * @param array $permissionNames
     * @return Role|null
     */
    public function assignPermissionsToRole(int $roleId, array $permissionNames): ?Role
    {
        $role = $this->getRoleById($roleId);

        if (!$role) {
            return null;
        }

        $permissions = Permission::query()->whereIn('name', $permissionNames)->get();
        $role->syncPermissions($permissions);

        return $role->fresh('permissions');
    }

    /**
     * Create default roles for the system
     *
     * @return void
     */
    public function createDefaultRoles(): void
    {
        foreach (RoleEnum::cases() as $role) {
            Role::query()->firstOrCreate([
                'name' => $role->value,
                'guard_name' => 'api',
            ]);
        }
    }

    /**
     * Create default roles and assign default permissions
     *
     * @return void
     */
    public function createDefaultRolesWithPermissions(): void
    {
        $this->createDefaultRoles();

        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::query()->where('name', $roleEnum->value)->first();
            if (!$role) continue;

            $defaultPermissions = $roleEnum->getDefaultPermissions();

            // Skip if the role has wildcard permissions (super admin)
            if (in_array('*', $defaultPermissions)) {
                continue;
            }

            $permissions = Permission::query()->whereIn('name', $defaultPermissions)->get();
            $role->syncPermissions($permissions);
        }
    }

    /**
     * Get default permissions for a role
     *
     * @param string $roleName
     * @return array
     */
    public function getDefaultPermissionsForRole(string $roleName): array
    {
        try {
            $roleEnum = RoleEnum::from($roleName);
            return $roleEnum->getDefaultPermissions();
        } catch (\ValueError $e) {
            return [];
        }
    }
}
