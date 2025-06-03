<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignPermissionsRequest;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class RoleController
 *
 * Controller for managing roles
 *
 * @package App\Http\Controllers\API
 */
class RoleController extends Controller
{
    /**
     * The role service instance.
     *
     * @var RoleService
     */
    protected RoleService $roleService;

    /**
     * Create a new controller instance.
     *
     * @param RoleService $roleService
     * @return void
     */
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of the users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $relations = $request->query('with', ['permissions']);
        $deleted = $request->boolean('deleted', null);
        $term = $request->query('term', '');

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $roles = $this->roleService->getAllRoles($term, $perPage, $relations, $deleted);

        return response()->success(
            RoleResource::collection($roles),
            'Roles retrieved successfully'
        );
    }

    /**
     * Store a newly created role in storage.
     *
     * @param RoleRequest $request
     * @return JsonResponse
     */
    public function store(RoleRequest $request): JsonResponse
    {
        $role = $this->roleService->createRole($request->validated());
        return response()->success(new RoleResource($role), 'Role created successfully', 201);
    }

    /**
     * Display the specified role.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $relations = $request->query('with', ['permissions']);

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $role = $this->roleService->getRoleById($id, $relations);

        if (!$role) {
            return response()->error('Role not found', null, 404);
        }

        return response()->success(new RoleResource($role), 'Role retrieved successfully');
    }

    /**
     * Update the specified role in storage.
     *
     * @param RoleRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(RoleRequest $request, int $id): JsonResponse
    {
        $role = $this->roleService->updateRole($id, $request->validated());

        if (!$role) {
            return response()->error('Role not found', null, 404);
        }

        return response()->success(new RoleResource($role), 'Role updated successfully');
    }

    /**
     * Remove the specified role from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $force = $request->boolean('force');

        $deleted = $this->roleService->deleteRole($id, $force);

        if (!$deleted) {
            return response()->error('Role not found or cannot be deleted', null, 404);
        }

        return response()->success(null, 'Role deleted successfully');
    }

    /**
     * Assign permissions to a role.
     *
     * @param AssignPermissionsRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function assignPermissions(AssignPermissionsRequest $request, int $id): JsonResponse
    {
        $role = $this->roleService->assignPermissionsToRole($id, $request->permissions);

        if (!$role) {
            return response()->error('Role not found', null, 404);
        }

        return response()->success(new RoleResource($role), 'Permissions assigned successfully');
    }

    /**
     * Create default roles for the system.
     *
     * @return JsonResponse
     */
    public function createDefaultRoles(): JsonResponse
    {
        $this->roleService->createDefaultRoles();
        return response()->success(null, 'Default roles created successfully');
    }
}
