<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExamRequest;
use App\Http\Resources\ExamResource;
use App\Services\ExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * The exam service instance.
     *
     * @var ExamService
     */
    protected ExamService $examService;

    /**
     * Create a new controller instance.
     *
     * @param ExamService $examService
     * @return void
     */
    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }

    /**
     * Display a listing of the exams.
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

        $exams = $this->examService->getAllExams($perPage, $relations);

        return response()->success(
            ExamResource::collection($exams),
            'Exams retrieved successfully'
        );
    }

    /**
     * Store a newly created exam in storage.
     *
     * @param ExamRequest $request
     * @return JsonResponse
     */
    public function store(ExamRequest $request): JsonResponse
    {
        $exam = $this->examService->createExam($request->validated());

        return response()->success(
            new ExamResource($exam),
            'Exam created successfully',
            201
        );
    }

    /**
     * Display the specified exam.
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

        $exam = $this->examService->getExamById($id, $relations);

        if (!$exam) {
            return response()->error('Exam not found', null, 404);
        }

        return response()->success(
            new ExamResource($exam),
            'Exam retrieved successfully'
        );
    }

    /**
     * Update the specified exam in storage.
     *
     * @param ExamRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(ExamRequest $request, int $id): JsonResponse
    {
        $exam = $this->examService->updateExam($id, $request->validated());

        if (!$exam) {
            return response()->error('Exam not found', null, 404);
        }

        return response()->success(
            new ExamResource($exam),
            'Exam updated successfully'
        );
    }

    /**
     * Remove the specified exam from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->examService->deleteExam($id);

        if (!$deleted) {
            return response()->error('Exam not found', null, 404);
        }

        return response()->success(
            null,
            'Exam deleted successfully'
        );
    }

    /**
     * Get exams by course.
     *
     * @param Request $request
     * @param int $courseId
     * @return JsonResponse
     */
    public function getByCourse(Request $request, int $courseId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $exams = $this->examService->getExamsByCourse($courseId, $perPage);

        return response()->success(
            ExamResource::collection($exams),
            'Exams retrieved successfully'
        );
    }

    /**
     * Get exams by semester.
     *
     * @param Request $request
     * @param int $semesterId
     * @return JsonResponse
     */
    public function getBySemester(Request $request, int $semesterId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $exams = $this->examService->getExamsBySemester($semesterId, $perPage);

        return response()->success(
            ExamResource::collection($exams),
            'Exams retrieved successfully'
        );
    }

    /**
     * Get upcoming exams.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUpcoming(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $exams = $this->examService->getUpcomingExams($perPage);

        return response()->success(
            ExamResource::collection($exams),
            'Upcoming exams retrieved successfully'
        );
    }

    /**
     * Update exam status.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,ongoing,completed,cancelled',
        ]);

        $exam = $this->examService->updateExamStatus($id, $request->status);

        if (!$exam) {
            return response()->error('Exam not found', null, 404);
        }

        return response()->success(
            new ExamResource($exam),
            'Exam status updated successfully'
        );
    }
}
