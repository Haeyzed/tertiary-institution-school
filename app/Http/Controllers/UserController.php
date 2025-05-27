<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * The user service instance.
     *
     * @var UserService
     */
    protected UserService $userService;

    /**
     * Create a new controller instance.
     *
     * @param UserService $userService
     * @return void
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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
        $users = $this->userService->getAllUsers($perPage);

        return response()->success(
            UserResource::collection($users),
            'Users retrieved successfully'
        );
    }

    /**
     * Store a newly created user in storage.
     *
     * @param UserRequest $request
     * @return JsonResponse
     */
    public function store(UserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return response()->success(
            new UserResource($user),
            'User created successfully',
            201
        );
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

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
        $user = $this->userService->updateUser($id, $request->validated());

        if (!$user) {
            return response()->error('User not found', null, 404);
        }

        return response()->success(
            new UserResource($user),
            'User updated successfully'
        );
    }

    /**
     * Remove the specified user from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
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
        $request->validate([
            'term' => 'required|string|min:2',
        ]);

        $perPage = $request->query('per_page', config('app.per_page'));
        $users = $this->userService->searchUsers($request->term, $perPage);

        return response()->success(
            UserResource::collection($users),
            'Search results retrieved successfully'
        );
    }
}
