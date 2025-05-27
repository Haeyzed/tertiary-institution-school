<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $departments = $this->departmentService->getAllDepartments($perPage, $relations);

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
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->departmentService->deleteDepartment($id);

        if (!$deleted) {
            return response()->error('Department not found', null, 404);
        }

        return response()->success(
            null,
            'Department deleted successfully'
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

    /**
     * Search departments by name or code.
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
        $departments = $this->departmentService->searchDepartments($request->term, $perPage);

        return response()->success(
            DepartmentResource::collection($departments),
            'Search results retrieved successfully'
        );
    }
}
