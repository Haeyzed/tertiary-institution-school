<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PermissionController
 *
 * Controller for managing permissions
 *
 * @package App\Http\Controllers\API
 */
class PermissionController extends Controller
{
    /**
     * The permission service instance.
     *
     * @var PermissionService
     */
    protected PermissionService $permissionService;

    /**
     * Create a new controller instance.
     *
     * @param PermissionService $permissionService
     * @return void
     */
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Display a listing of the permissions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $relations = $request->query('with', ['roles']);

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $permissions = $this->permissionService->getAllPermissions($perPage, $relations);

        return response()->success(
            PermissionResource::collection($permissions),
            'Permissions retrieved successfully'
        );
    }

    /**
     * Store a newly created permission in storage.
     *
     * @param PermissionRequest $request
     * @return JsonResponse
     */
    public function store(PermissionRequest $request): JsonResponse
    {
        $permission = $this->permissionService->createPermission($request->validated());
        return response()->success(new PermissionResource($permission), 'Permission created successfully', 201);
    }

    /**
     * Display the specified permission.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $relations = $request->query('with', ['roles']);

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $permission = $this->permissionService->getPermissionById($id, $relations);

        if (!$permission) {
            return response()->error('Permission not found', null, 404);
        }

        return response()->success(new PermissionResource($permission), 'Permission retrieved successfully');
    }

    /**
     * Update the specified permission in storage.
     *
     * @param PermissionRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(PermissionRequest $request, int $id): JsonResponse
    {
        $permission = $this->permissionService->updatePermission($id, $request->validated());

        if (!$permission) {
            return response()->error('Permission not found', null, 404);
        }

        return response()->success(new PermissionResource($permission), 'Permission updated successfully');
    }

    /**
     * Remove the specified permission from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->permissionService->deletePermission($id);

        if (!$result) {
            return response()->error('Permission not found', null, 404);
        }

        return response()->success(null, 'Permission deleted successfully');
    }

    /**
     * Create default permissions for the system.
     *
     * @return JsonResponse
     */
    public function createDefaultPermissions(): JsonResponse
    {
        $this->permissionService->createDefaultPermissions();
        return response()->success(null, 'Default permissions created successfully');
    }
}
