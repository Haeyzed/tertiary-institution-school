<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignRolesRequest;
use App\Http\Requests\AssignPermissionsRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\UserResource;
use App\Services\ACLService;
use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Class ACLController
 *
 * Controller for managing Access Control Lists (roles and permissions for users)
 *
 * @package App\Http\Controllers\API
 */
class ACLController extends Controller
{
    /**
     * The ACL service instance.
     *
     * @var ACLService
     */
    protected ACLService $aclService;

    /**
     * Create a new controller instance.
     *
     * @param ACLService $aclService
     * @return void
     */
    public function __construct(ACLService $aclService)
    {
        $this->aclService = $aclService;
    }

    /**
     * Get all roles for a user.
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getUserRoles(int $userId): JsonResponse
    {
        try {
            $roles = $this->aclService->getUserRoles($userId);
            return response()->success(RoleResource::collection($roles));
        } catch (Exception $e) {
            return response()->error('User not found', null, 404);
        }
    }

    /**
     * Get all direct permissions for a user.
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getUserDirectPermissions(int $userId): JsonResponse
    {
        try {
            $permissions = $this->aclService->getUserDirectPermissions($userId);
            return response()->success(PermissionResource::collection($permissions));
        } catch (Exception $e) {
            return response()->error('User not found', null, 404);
        }
    }

    /**
     * Get all permissions for a user (including permissions from roles).
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getUserAllPermissions(int $userId): JsonResponse
    {
        try {
            $permissions = $this->aclService->getUserAllPermissions($userId);
            return response()->success(PermissionResource::collection($permissions));
        } catch (Exception $e) {
            return response()->error('User not found', null, 404);
        }
    }

    /**
     * Assign roles to a user.
     *
     * @param AssignRolesRequest $request
     * @param int $userId
     * @return JsonResponse
     */
    public function assignRolesToUser(AssignRolesRequest $request, int $userId): JsonResponse
    {
        try {
            $user = $this->aclService->assignRolesToUser($userId, $request->roles);
            return response()->success(new UserResource($user), 'Roles assigned successfully');
        } catch (Exception $e) {
            return response()->error('User not found', null, 404);
        }
    }

    /**
     * Assign direct permissions to a user.
     *
     * @param AssignPermissionsRequest $request
     * @param int $userId
     * @return JsonResponse
     */
    public function assignPermissionsToUser(AssignPermissionsRequest $request, int $userId): JsonResponse
    {
        try {
            $user = $this->aclService->assignPermissionsToUser($userId, $request->permissions);
            return response()->success(new UserResource($user), 'Permissions assigned successfully');
        } catch (Exception $e) {
            return response()->error('User not found', null, 404);
        }
    }

    /**
     * Remove roles from a user.
     *
     * @param AssignRolesRequest $request
     * @param int $userId
     * @return JsonResponse
     */
    public function removeRolesFromUser(AssignRolesRequest $request, int $userId): JsonResponse
    {
        try {
            $user = $this->aclService->removeRolesFromUser($userId, $request->roles);
            return response()->success(new UserResource($user), 'Roles removed successfully');
        } catch (Exception $e) {
            return response()->error('User not found', null, 404);
        }
    }

    /**
     * Remove direct permissions from a user.
     *
     * @param AssignPermissionsRequest $request
     * @param int $userId
     * @return JsonResponse
     */
    public function removePermissionsFromUser(AssignPermissionsRequest $request, int $userId): JsonResponse
    {
        try {
            $user = $this->aclService->removePermissionsFromUser($userId, $request->permissions);
            return response()->success(new UserResource($user), 'Permissions removed successfully');
        } catch (Exception $e) {
            return response()->error('User not found', null, 404);
        }
    }

    /**
     * Check if a user has a specific role.
     *
     * @param int $userId
     * @param string $roleName
     * @return JsonResponse
     */
    public function checkUserHasRole(int $userId, string $roleName): JsonResponse
    {
        try {
            $hasRole = $this->aclService->userHasRole($userId, $roleName);
            return response()->success(['has_role' => $hasRole]);
        } catch (Exception $e) {
            return response()->error('User not found', null, 404);
        }
    }

    /**
     * Check if a user has a specific permission.
     *
     * @param int $userId
     * @param string $permissionName
     * @return JsonResponse
     */
    public function checkUserHasPermission(int $userId, string $permissionName): JsonResponse
    {
        try {
            $hasPermission = $this->aclService->userHasPermission($userId, $permissionName);
            return response()->success(['has_permission' => $hasPermission]);
        } catch (Exception $e) {
            return response()->error('User not found', null, 404);
        }
    }
}
