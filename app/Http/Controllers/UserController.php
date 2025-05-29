<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfilePhotoRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UploadResource;
use App\Http\Resources\UserResource;
use App\Services\ACLService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class UserController extends Controller
{
    /**
     * The user service instance.
     *
     * @var UserService
     */
    protected UserService $userService;

    /**
     * The ACL service instance.
     *
     * @var ACLService
     */
    protected ACLService $aclService;

    /**
     * Create a new controller instance.
     *
     * @param UserService $userService
     * @param ACLService $aclService
     * @return void
     */
    public function __construct(UserService $userService, ACLService $aclService)
    {
        $this->userService = $userService;
        $this->aclService = $aclService;
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if (!$this->checkPermission('view-user')) {
            return response()->error('Unauthorized', null, 403);
        }

        $perPage = $request->query('per_page', config('app.per_page'));
        $relations = $request->query('with', []);
        $userType = $request->query('user_type');

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $users = $this->userService->getAllUsers($perPage, $relations, $userType);

        if ($perPage) {
            return response()->paginated(
                UserResource::collection($users),
                'Users retrieved successfully'
            );
        }

        return response()->success(
            UserResource::collection($users),
            'Users retrieved successfully'
        );
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
     * Store a newly created user in storage.
     *
     * @param UserRequest $request
     * @return JsonResponse
     */
    public function store(UserRequest $request): JsonResponse
    {
        if (!$this->checkPermission('create-user')) {
            return response()->error('Unauthorized', null, 403);
        }

        try {
            $relations = $request->input('with', []);

            if (is_string($relations)) {
                $relations = explode(',', $relations);
            }

            $user = $this->userService->createUser($request->validated(), $relations);

            return response()->success(
                new UserResource($user),
                'User created successfully',
                201
            );
        } catch (InvalidArgumentException $e) {
            return response()->error($e->getMessage(), null, 422);
        }
    }

    /**
     * Display the specified user.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        if (!$this->checkPermission('view-user')) {
            return response()->error('Unauthorized', null, 403);
        }

        $relations = $request->query('with', []);

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $user = $this->userService->getUserById($id, $relations);

        if (!$user) {
            return response()->error('User not found', null, 404);
        }

        return response()->success(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Update the specified user in storage.
     *
     * @param UserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UserRequest $request, int $id): JsonResponse
    {
        if (!$this->checkPermission('edit-user')) {
            return response()->error('Unauthorized', null, 403);
        }

        try {
            $relations = $request->input('with', []);

            if (is_string($relations)) {
                $relations = explode(',', $relations);
            }

            $user = $this->userService->updateUser($id, $request->validated(), $relations);

            if (!$user) {
                return response()->error('User not found', null, 404);
            }

            return response()->success(
                new UserResource($user),
                'User updated successfully'
            );
        } catch (InvalidArgumentException $e) {
            return response()->error($e->getMessage(), null, 422);
        }
    }

    /**
     * Remove the specified user from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        if (!$this->checkPermission('delete-user')) {
            return response()->error('Unauthorized', null, 403);
        }

        $deleted = $this->userService->deleteUser($id);

        if (!$deleted) {
            return response()->error('User not found', null, 404);
        }

        return response()->success(
            null,
            'User deleted successfully'
        );
    }

    /**
     * Search for users by name or email.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        if (!$this->checkPermission('view-user')) {
            return response()->error('Unauthorized', null, 403);
        }

        $request->validate([
            'term' => 'required|string|min:2',
        ]);

        $perPage = $request->query('per_page', config('app.per_page'));
        $relations = $request->input('with', []);
        $userType = $request->query('user_type');

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $users = $this->userService->searchUsers($request->term, $perPage, $relations, $userType);

        if ($perPage) {
            return response()->paginated(
                UserResource::collection($users),
                'Search results retrieved successfully'
            );
        }

        return response()->success(
            UserResource::collection($users),
            'Search results retrieved successfully'
        );
    }

    /**
     * Upload profile photo for a user.
     *
     * @param ProfilePhotoRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function uploadProfilePhoto(ProfilePhotoRequest $request, int $id): JsonResponse
    {
        if (!$this->checkPermission('edit-user')) {
            return response()->error('Unauthorized', null, 403);
        }

        try {
            $result = $this->userService->uploadUserProfilePhoto($id, $request->file('photo'));

            return response()->success([
                'user' => new UserResource($result['user']),
                'upload' => new UploadResource($result['upload']),
                'photo_url' => $result['photo_url'],
                'thumbnails' => $result['thumbnails'],
            ], 'Profile photo uploaded successfully');

        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                null,
                422
            );
        }
    }

    /**
     * Remove profile photo for a user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function removeProfilePhoto(int $id): JsonResponse
    {
        if (!$this->checkPermission('edit-user')) {
            return response()->error('Unauthorized', null, 403);
        }

        try {
            $user = $this->userService->removeUserProfilePhoto($id);

            return response()->success(
                new UserResource($user),
                'Profile photo removed successfully'
            );

        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                null,
                400
            );
        }
    }

    /**
     * Get user uploads.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getUserUploads(Request $request, int $id): JsonResponse
    {
        if (!$this->checkPermission('view-user')) {
            return response()->error('Unauthorized', null, 403);
        }

        try {
            $perPage = $request->query('per_page', config('app.per_page'));
            $fileType = $request->query('file_type');
            $disk = $request->query('disk');

            $uploads = $this->userService->getUserUploads($id, $perPage, $fileType, $disk);

            return response()->paginated(
                UploadResource::collection($uploads),
                'User uploads retrieved successfully'
            );

        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                null,
                404
            );
        }
    }

    /**
     * Get user upload statistics.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getUserUploadStatistics(int $id): JsonResponse
    {
        if (!$this->checkPermission('view-user')) {
            return response()->error('Unauthorized', null, 403);
        }

        try {
            $stats = $this->userService->getUserUploadStatistics($id);

            return response()->success(
                $stats,
                'User upload statistics retrieved successfully'
            );

        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                null,
                404
            );
        }
    }

    /**
     * Assign roles to a user.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function assignRoles(Request $request, int $id): JsonResponse
    {
        if (!$this->checkPermission('assign-role')) {
            return response()->error('Unauthorized', null, 403);
        }

        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->error('User not found', null, 404);
        }

        $user->syncRoles($request->roles);

        return response()->success(
            new UserResource($user->load('roles')),
            'Roles assigned successfully'
        );
    }

    /**
     * Assign permissions to a user.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function assignPermissions(Request $request, int $id): JsonResponse
    {
        if (!$this->checkPermission('assign-permission')) {
            return response()->error('Unauthorized', null, 403);
        }

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->error('User not found', null, 404);
        }

        $user->syncPermissions($request->permissions);

        return response()->success(
            new UserResource($user->load('permissions')),
            'Permissions assigned successfully'
        );
    }

    /**
     * Get user's roles and permissions.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getUserPermissions(int $id): JsonResponse
    {
        if (!$this->checkPermission('view-user')) {
            return response()->error('Unauthorized', null, 403);
        }

        $user = $this->userService->getUserById($id, ['roles', 'permissions']);

        if (!$user) {
            return response()->error('User not found', null, 404);
        }

        return response()->success([
            'user' => new UserResource($user),
            'roles' => $user->roles->pluck('name'),
            'direct_permissions' => $user->permissions->pluck('name'),
            'all_permissions' => $user->getAllPermissions()->pluck('name')->unique()->values(),
        ], 'User permissions retrieved successfully');
    }
}
