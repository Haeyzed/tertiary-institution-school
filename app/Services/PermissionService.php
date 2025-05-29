<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

/**
 * Class PermissionService
 *
 * Service for managing permissions in the system
 *
 * @package App\Services
 */
class PermissionService
{
    /**
     * Get all permissions with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllPermissions(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Permission::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get a permission by name.
     *
     * @param string $name
     * @param array $relations
     * @return Permission|null
     */
    public function getPermissionByName(string $name, array $relations = []): ?Permission
    {
        $query = Permission::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->where('name', $name)->first();
    }

    /**
     * Create a new permission
     *
     * @param array $data
     * @return Permission
     */
    public function createPermission(array $data): Permission
    {
        $permission = Permission::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        if (isset($data['roles']) && is_array($data['roles'])) {
            $permission->syncRoles($data['roles']);
        }

        return $permission;
    }

    /**
     * Update an existing permission
     *
     * @param int $id
     * @param array $data
     * @return Permission|null
     */
    public function updatePermission(int $id, array $data): ?Permission
    {
        $permission = $this->getPermissionById($id);

        if (!$permission) {
            return null;
        }

        $permission->update([
            'name' => $data['name'] ?? $permission->name,
            'guard_name' => $data['guard_name'] ?? $permission->guard_name,
        ]);

        if (isset($data['roles']) && is_array($data['roles'])) {
            $permission->syncRoles($data['roles']);
        }

        return $permission->fresh('roles');
    }

    /**
     * Get a user by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Permission|null
     */
    public function getPermissionById(int $id, array $relations = []): ?Permission
    {
        $query = Permission::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Delete a permission
     *
     * @param int $id
     * @return bool
     */
    public function deletePermission(int $id): bool
    {
        $permission = $this->getPermissionById($id);

        if (!$permission) {
            return false;
        }

        return $permission->delete();
    }

    /**
     * Create default permissions for the system
     *
     * @return void
     */
    public function createDefaultPermissions(): void
    {
        $modules = [
            'user', 'role', 'permission', 'faculty', 'department', 'program',
            'academic-session', 'semester', 'course', 'staff', 'student', 'parent',
            'timetable', 'assignment', 'exam', 'result', 'fee', 'payment',
            'notification', 'announcement', 'dashboard'
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::query()->firstOrCreate([
                    'name' => "{$action}-{$module}",
                    'guard_name' => 'api',
                ]);
            }
        }

        // Additional specific permissions
        $additionalPermissions = [
            'assign-role', 'revoke-role', 'assign-permission', 'revoke-permission',
            'view-all-students', 'view-all-staff', 'view-all-parents',
            'manage-settings', 'view-reports', 'export-data', 'import-data',
            'process-payment', 'verify-payment', 'manage-payment-methods',
            'view-dashboard-metrics'
        ];

        foreach ($additionalPermissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }
    }
}
