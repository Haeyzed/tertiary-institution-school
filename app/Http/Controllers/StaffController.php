<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Http\Resources\StaffResource;
use App\Services\StaffService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class StaffController extends Controller
{
    /**
     * The staff service instance.
     *
     * @var StaffService
     */
    protected StaffService $staffService;

    /**
     * Create a new controller instance.
     *
     * @param StaffService $staffService
     * @return void
     */
    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    /**
     * Display a listing of the staff.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $relations = $request->query('with', []);
        $deleted = $request->boolean('deleted', null);
        $term = $request->query('term', '');

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $staff = $this->staffService->getAllStaff($term, $perPage, $relations, $deleted);

        return response()->success(
            StaffResource::collection($staff),
            'Staff retrieved successfully'
        );
    }

    /**
     * Store a newly created staff in storage.
     *
     * @param StaffRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(StaffRequest $request): JsonResponse
    {
        try {
            $staff = $this->staffService->createStaff($request->validated());

            return response()->success(
                new StaffResource($staff),
                'Staff created successfully',
                201
            );
        } catch (Exception $e) {
            return response()->error(
                'Failed to create staff',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Display the specified staff.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $relations = $request->query('with', []);

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $staff = $this->staffService->getStaffById($id, $relations);

        if (!$staff) {
            return response()->error('Staff not found', null, 404);
        }

        return response()->success(
            new StaffResource($staff),
            'Staff retrieved successfully'
        );
    }

    /**
     * Update the specified staff in storage.
     *
     * @param StaffRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(StaffRequest $request, int $id): JsonResponse
    {
        try {
            $staff = $this->staffService->updateStaff($id, $request->validated());

            if (!$staff) {
                return response()->error('Staff not found', null, 404);
            }

            return response()->success(
                new StaffResource($staff),
                'Staff updated successfully'
            );
        } catch (Exception $e) {
            return response()->error(
                'Failed to update staff',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Remove the specified staff from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws Throwable
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $force = $request->boolean('force');

        try {
            $deleted = $this->staffService->deleteStaff($id, $force);

            if (!$deleted) {
                return response()->error('Staff not found', null, 404);
            }

            return response()->success(
                null,
                'Staff deleted successfully'
            );
        } catch (Exception $e) {
            return response()->error(
                'Failed to delete staff',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Restore a soft-deleted staff.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $restored = $this->staffService->restoreStaff($id);

        if (!$restored) {
            return response()->error('Staff not found or not deleted', null, 404);
        }

        return response()->success(
            new StaffResource($restored),
            'Staff restored successfully'
        );
    }

    /**
     * Get staff by department.
     *
     * @param Request $request
     * @param int $departmentId
     * @return JsonResponse
     */
    public function getByDepartment(Request $request, int $departmentId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $staff = $this->staffService->getStaffByDepartment($departmentId, $perPage);

        return response()->success(
            StaffResource::collection($staff),
            'Staff retrieved successfully'
        );
    }

    /**
     * Search staff by name, email, or staff ID.
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
        $staff = $this->staffService->searchStaff($request->term, $perPage);

        return response()->success(
            StaffResource::collection($staff),
            'Search results retrieved successfully'
        );
    }
}
