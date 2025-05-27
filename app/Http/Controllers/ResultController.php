<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResultRequest;
use App\Http\Resources\ResultResource;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ResultController extends Controller
{
    /**
     * The result service instance.
     *
     * @var ResultService
     */
    protected ResultService $resultService;

    /**
     * Create a new controller instance.
     *
     * @param ResultService $resultService
     * @return void
     */
    public function __construct(ResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    /**
     * Display a listing of the results.
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

        $results = $this->resultService->getAllResults($perPage, $relations);

        return response()->success(
            ResultResource::collection($results),
            'Results retrieved successfully'
        );
    }

    /**
     * Store a newly created result in storage.
     *
     * @param ResultRequest $request
     * @return JsonResponse
     */
    public function store(ResultRequest $request): JsonResponse
    {
        $result = $this->resultService->createResult($request->validated());

        return response()->success(
            new ResultResource($result),
            'Result created successfully',
            201
        );
    }

    /**
     * Display the specified result.
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

        $result = $this->resultService->getResultById($id, $relations);

        if (!$result) {
            return response()->error('Result not found', null, 404);
        }

        return response()->success(
            new ResultResource($result),
            'Result retrieved successfully'
        );
    }

    /**
     * Update the specified result in storage.
     *
     * @param ResultRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(ResultRequest $request, int $id): JsonResponse
    {
        $result = $this->resultService->updateResult($id, $request->validated());

        if (!$result) {
            return response()->error('Result not found', null, 404);
        }

        return response()->success(
            new ResultResource($result),
            'Result updated successfully'
        );
    }

    /**
     * Remove the specified result from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->resultService->deleteResult($id);

        if (!$deleted) {
            return response()->error('Result not found', null, 404);
        }

        return response()->success(
            null,
            'Result deleted successfully'
        );
    }

    /**
     * Get results by student.
     *
     * @param Request $request
     * @param int $studentId
     * @return JsonResponse
     */
    public function getByStudent(Request $request, int $studentId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $results = $this->resultService->getResultsByStudent($studentId, $perPage);

        return response()->success(
            ResultResource::collection($results),
            'Results retrieved successfully'
        );
    }

    /**
     * Get results by course.
     *
     * @param Request $request
     * @param int $courseId
     * @return JsonResponse
     */
    public function getByCourse(Request $request, int $courseId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $results = $this->resultService->getResultsByCourse($courseId, $perPage);

        return response()->success(
            ResultResource::collection($results),
            'Results retrieved successfully'
        );
    }

    /**
     * Get results by exam.
     *
     * @param Request $request
     * @param int $examId
     * @return JsonResponse
     */
    public function getByExam(Request $request, int $examId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $results = $this->resultService->getResultsByExam($examId, $perPage);

        return response()->success(
            ResultResource::collection($results),
            'Results retrieved successfully'
        );
    }

    /**
     * Get results by semester.
     *
     * @param Request $request
     * @param int $semesterId
     * @return JsonResponse
     */
    public function getBySemester(Request $request, int $semesterId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $results = $this->resultService->getResultsBySemester($semesterId, $perPage);

        return response()->success(
            ResultResource::collection($results),
            'Results retrieved successfully'
        );
    }

    /**
     * Get student's semester GPA.
     *
     * @param int $studentId
     * @param int $semesterId
     * @return JsonResponse
     */
    public function getStudentSemesterGPA(int $studentId, int $semesterId): JsonResponse
    {
        $gpa = $this->resultService->getStudentSemesterGPA($studentId, $semesterId);

        if ($gpa === null) {
            return response()->error('No results found for this student in this semester', null, 404);
        }

        return response()->success(
            ['gpa' => $gpa],
            'Student semester GPA retrieved successfully'
        );
    }

    /**
     * Get student's cumulative GPA.
     *
     * @param int $studentId
     * @return JsonResponse
     */
    public function getStudentCumulativeGPA(int $studentId): JsonResponse
    {
        $gpa = $this->resultService->getStudentCumulativeGPA($studentId);

        if ($gpa === null) {
            return response()->error('No results found for this student', null, 404);
        }

        return response()->success(
            ['gpa' => $gpa],
            'Student cumulative GPA retrieved successfully'
        );
    }
}
