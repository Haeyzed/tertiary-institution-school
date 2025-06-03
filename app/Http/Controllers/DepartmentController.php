<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * The department service instance.
     *
     * @var DepartmentService
     */
    protected DepartmentService $departmentService;

    /**
     * Create a new controller instance.
     *
     * @param DepartmentService $departmentService
     * @return void
     */
    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    /**
     * Display a listing of the departments.
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

        $departments = $this->departmentService->getAllDepartments($term, $perPage, $relations, $deleted);

        return response()->success(
            DepartmentResource::collection($departments),
            'Departments retrieved successfully'
        );
    }

    /**
     * Store a newly created department in storage.
     *
     * @param DepartmentRequest $request
     * @return JsonResponse
     */
    public function store(DepartmentRequest $request): JsonResponse
    {
        $department = $this->departmentService->createDepartment($request->validated());

        return response()->success(
            new DepartmentResource($department),
            'Department created successfully',
            201
        );
    }

    /**
     * Display the specified department.
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

        $department = $this->departmentService->getDepartmentById($id, $relations);

        if (!$department) {
            return response()->error('Department not found', null, 404);
        }

        return response()->success(
            new DepartmentResource($department),
            'Department retrieved successfully'
        );
    }

    /**
     * Update the specified department in storage.
     *
     * @param DepartmentRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(DepartmentRequest $request, int $id): JsonResponse
    {
        $department = $this->departmentService->updateDepartment($id, $request->validated());

        if (!$department) {
            return response()->error('Department not found', null, 404);
        }

        return response()->success(
            new DepartmentResource($department),
            'Department updated successfully'
        );
    }

    /**
     * Remove the specified department from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $force = $request->boolean('force');

        $deleted = $this->departmentService->deleteDepartment($id, $force);

        if (!$deleted) {
            return response()->error('Department not found', null, 404);
        }

        return response()->success(
            null,
            'Department deleted successfully'
        );
    }

    /**
     * Restore a soft-deleted department.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $restored = $this->departmentService->restoreDepartment($id);

        if (!$restored) {
            return response()->error('Department not found or not deleted', null, 404);
        }

        return response()->success(
            new DepartmentResource($restored),
            'Department restored successfully'
        );
    }

    /**
     * Get departments by faculty.
     *
     * @param Request $request
     * @param int $facultyId
     * @return JsonResponse
     */
    public function getByFaculty(Request $request, int $facultyId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $departments = $this->departmentService->getDepartmentsByFaculty($facultyId, $perPage);

        return response()->success(
            DepartmentResource::collection($departments),
            'Departments retrieved successfully'
        );
    }
}
