<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeRequest;
use App\Http\Resources\GradeResource;
use App\Services\GradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    /**
     * The grade service instance.
     *
     * @var GradeService
     */
    protected GradeService $gradeService;

    /**
     * Create a new controller instance.
     *
     * @param GradeService $gradeService
     * @return void
     */
    public function __construct(GradeService $gradeService)
    {
        $this->gradeService = $gradeService;
    }

    /**
     * Display a listing of the grades.
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

        $grades = $this->gradeService->getAllGrades($perPage, $relations);

        return response()->success(
            GradeResource::collection($grades),
            'Grades retrieved successfully'
        );
    }

    /**
     * Store a newly created grade in storage.
     *
     * @param GradeRequest $request
     * @return JsonResponse
     */
    public function store(GradeRequest $request): JsonResponse
    {
        $grade = $this->gradeService->createGrade($request->validated());

        return response()->success(
            new GradeResource($grade),
            'Grade created successfully',
            201
        );
    }

    /**
     * Display the specified grade.
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

        $grade = $this->gradeService->getGradeById($id, $relations);

        if (!$grade) {
            return response()->error('Grade not found', null, 404);
        }

        return response()->success(
            new GradeResource($grade),
            'Grade retrieved successfully'
        );
    }

    /**
     * Update the specified grade in storage.
     *
     * @param GradeRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(GradeRequest $request, int $id): JsonResponse
    {
        $grade = $this->gradeService->updateGrade($id, $request->validated());

        if (!$grade) {
            return response()->error('Grade not found', null, 404);
        }

        return response()->success(
            new GradeResource($grade),
            'Grade updated successfully'
        );
    }

    /**
     * Remove the specified grade from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->gradeService->deleteGrade($id);

        if (!$deleted) {
            return response()->error('Grade not found', null, 404);
        }

        return response()->success(
            null,
            'Grade deleted successfully'
        );
    }

    /**
     * Get grade by score.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getByScore(Request $request): JsonResponse
    {
        $request->validate([
            'score' => 'required|numeric|min:0|max:100',
        ]);

        $grade = $this->gradeService->getGradeByScore($request->score);

        if (!$grade) {
            return response()->error('No grade found for this score', null, 404);
        }

        return response()->success(
            new GradeResource($grade),
            'Grade retrieved successfully'
        );
    }
}
