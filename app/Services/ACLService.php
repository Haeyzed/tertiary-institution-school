<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use ValueError;

class ACLService
{
    /**
     * Get all roles for a user
     *
     * @param int $userId
     * @return Collection
     */
    public function getUserRoles(int $userId): Collection
    {
        $user = User::query()->findOrFail($userId);
        return $user->roles;
    }

    /**
     * Get all direct permissions for a user
     *
     * @param int $userId
     * @return Collection
     */
    public function getUserDirectPermissions(int $userId): Collection
    {
        $user = User::query()->findOrFail($userId);
        return $user->permissions;
    }

    /**
     * Get all permissions for a user (including permissions from roles)
     *
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function getUserAllPermissions(int $userId): \Illuminate\Support\Collection
    {
        $user = User::query()->findOrFail($userId);
        return $user->getAllPermissions();
    }

    /**
     * Get all unique permissions for a user (filtering out duplicates)
     *
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function getUserUniquePermissions(int $userId): \Illuminate\Support\Collection
    {
        $user = User::query()->findOrFail($userId);
        return $user->getAllPermissions()->unique('id');
    }

    /**
     * Assign roles to a user
     *
     * @param int $userId
     * @param array $roleIds
     * @return User
     */
    public function assignRolesToUser(int $userId, array $roleIds): User
    {
        $user = User::query()->findOrFail($userId);
        $roles = Role::query()->whereIn('id', $roleIds)->get();

        $user->syncRoles($roles);

        return $user->fresh('roles');
    }

    /**
     * Assign direct permissions to a user
     *
     * @param int $userId
     * @param array $permissionIds
     * @return User
     */
    public function assignPermissionsToUser(int $userId, array $permissionIds): User
    {
        $user = User::query()->findOrFail($userId);
        $permissions = Permission::query()->whereIn('id', $permissionIds)->get();

        $user->syncPermissions($permissions);

        return $user->fresh('permissions');
    }

    /**
     * Remove roles from a user
     *
     * @param int $userId
     * @param array $roleIds
     * @return User
     */
    public function removeRolesFromUser(int $userId, array $roleIds): User
    {
        $user = User::query()->findOrFail($userId);
        $roles = Role::query()->whereIn('id', $roleIds)->get();

        foreach ($roles as $role) {
            $user->removeRole($role);
        }

        return $user->fresh('roles');
    }

    /**
     * Remove direct permissions from a user
     *
     * @param int $userId
     * @param array $permissionIds
     * @return User
     */
    public function removePermissionsFromUser(int $userId, array $permissionIds): User
    {
        $user = User::query()->findOrFail($userId);
        $permissions = Permission::query()->whereIn('id', $permissionIds)->get();

        foreach ($permissions as $permission) {
            $user->revokePermissionTo($permission);
        }

        return $user->fresh('permissions');
    }

    /**
     * Check if a user has a specific role
     *
     * @param int $userId
     * @param string $roleName
     * @return bool
     */
    public function userHasRole(int $userId, string $roleName): bool
    {
        $user = User::query()->findOrFail($userId);
        return $user->hasRole($roleName);
    }

    /**
     * Check if a user has a specific permission
     *
     * @param int $userId
     * @param string $permissionName
     * @return bool
     */
    public function userHasPermission(int $userId, string $permissionName): bool
    {
        $user = User::query()->findOrFail($userId);

        // Super admins have all permissions
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermissionTo($permissionName);
    }

    /**
     * Assign default roles based on user type
     *
     * @param User $user
     * @param string $userType
     * @return User
     */
    public function assignDefaultRolesByUserType(User $user, string $userType): User
    {
        try {
            $userTypeEnum = UserTypeEnum::from($userType);
            $defaultRole = $userTypeEnum->getDefaultRole();

            // Assign the default role
            $user->assignRole($defaultRole);

            // If it's a super admin, no need to assign specific permissions
            if ($userTypeEnum === UserTypeEnum::SUPER_ADMIN) {
                return $user->fresh('roles');
            }

            // Get the role enum
            $roleEnum = RoleEnum::from($defaultRole);

            // Get default permissions for this role
            $defaultPermissions = $roleEnum->getDefaultPermissions();

            // If the role has specific permissions (not wildcard), assign them
            if (!in_array('*', $defaultPermissions)) {
                $permissions = Permission::query()->whereIn('name', $defaultPermissions)->get();
                $user->syncPermissions($permissions);
            }

            return $user->fresh(['roles', 'permissions']);
        } catch (ValueError $e) {
            // Invalid user type, just return the user without assigning roles
            return $user;
        }
    }

    /**
     * Check if the authenticated user has the required permission.
     *
     * @param string $permission
     * @return bool
     */
    protected function checkPermission(string $permission): bool
    {
        $user = Auth::user();

        // Super admins have all permissions
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }

    /**
     * Create default roles and permissions for the system
     *
     * @return void
     */
    public function createDefaultRolesAndPermissions(): void
    {
        // Create default roles
        foreach (RoleEnum::cases() as $role) {
            Role::query()->firstOrCreate([
                'name' => $role->value,
                'guard_name' => 'api',
            ]);
        }

        // Create default permissions
        $this->createDefaultPermissions();

        // Assign default permissions to roles
        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::query()->where('name', $roleEnum->value)->first();
            if (!$role) continue;

            $defaultPermissions = $roleEnum->getDefaultPermissions();

            // Skip if the role has wildcard permissions
            if (in_array('*', $defaultPermissions)) {
                continue;
            }

            $permissions = Permission::query()->whereIn('name', $defaultPermissions)->get();
            $role->syncPermissions($permissions);
        }
    }

    /**
     * Create default permissions for the system
     *
     * @return void
     */
    private function createDefaultPermissions(): void
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
                Permission::firstOrCreate([
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
