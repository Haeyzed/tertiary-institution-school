<?php

namespace App\Http\Controllers;

use App\Http\Requests\SemesterRequest;
use App\Http\Resources\SemesterResource;
use App\Services\SemesterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    /**
     * The semester service instance.
     *
     * @var SemesterService
     */
    protected SemesterService $semesterService;

    /**
     * Create a new controller instance.
     *
     * @param SemesterService $semesterService
     * @return void
     */
    public function __construct(SemesterService $semesterService)
    {
        $this->semesterService = $semesterService;
    }

    /**
     * Display a listing of the semesters.
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

        $semesters = $this->semesterService->getAllSemesters($term, $perPage, $relations, $deleted);

        return response()->success(
            SemesterResource::collection($semesters),
            'Semesters retrieved successfully'
        );
    }

    /**
     * Store a newly created semester in storage.
     *
     * @param SemesterRequest $request
     * @return JsonResponse
     */
    public function store(SemesterRequest $request): JsonResponse
    {
        $semester = $this->semesterService->createSemester($request->validated());

        return response()->success(
            new SemesterResource($semester),
            'Semester created successfully',
            201
        );
    }

    /**
     * Display the specified semester.
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

        $semester = $this->semesterService->getSemesterById($id, $relations);

        if (!$semester) {
            return response()->error('Semester not found', null, 404);
        }

        return response()->success(
            new SemesterResource($semester),
            'Semester retrieved successfully'
        );
    }

    /**
     * Update the specified semester in storage.
     *
     * @param SemesterRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(SemesterRequest $request, int $id): JsonResponse
    {
        $semester = $this->semesterService->updateSemester($id, $request->validated());

        if (!$semester) {
            return response()->error('Semester not found', null, 404);
        }

        return response()->success(
            new SemesterResource($semester),
            'Semester updated successfully'
        );
    }

    /**
     * Remove the specified semester from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $force = $request->boolean('force');

        $deleted = $this->semesterService->deleteSemester($id, $force);

        if (!$deleted) {
            return response()->error('Semester not found', null, 404);
        }

        return response()->success(
            null,
            'Semester deleted successfully'
        );
    }

    /**
     * Restore a soft-deleted semester.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $restored = $this->semesterService->restoreSemester($id);

        if (!$restored) {
            return response()->error('Semester not found or not deleted', null, 404);
        }

        return response()->success(
            new SemesterResource($restored),
            'Semester restored successfully'
        );
    }

    /**
     * Get semesters by academic session.
     *
     * @param Request $request
     * @param int $academicSessionId
     * @return JsonResponse
     */
    public function getByAcademicSession(Request $request, int $academicSessionId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $semesters = $this->semesterService->getSemestersByAcademicSession($academicSessionId, $perPage);

        return response()->success(
            SemesterResource::collection($semesters),
            'Semesters retrieved successfully'
        );
    }

    /**
     * Get current semesters (semesters in the current academic session).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCurrent(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $semesters = $this->semesterService->getCurrentSemesters($perPage);

        return response()->success(
            SemesterResource::collection($semesters),
            'Current semesters retrieved successfully'
        );
    }
}
