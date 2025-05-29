<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignmentRequest;
use App\Http\Requests\StudentAssignmentRequest;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\StudentAssignmentResource;
use App\Services\AssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /**
     * The assignment service instance.
     *
     * @var AssignmentService
     */
    protected AssignmentService $assignmentService;

    /**
     * Create a new controller instance.
     *
     * @param AssignmentService $assignmentService
     * @return void
     */
    public function __construct(AssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Display a listing of the assignments.
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

        $assignments = $this->assignmentService->getAllAssignments($perPage, $relations);

        return response()->success(
            AssignmentResource::collection($assignments),
            'Assignments retrieved successfully'
        );
    }

    /**
     * Store a newly created assignment in storage.
     *
     * @param AssignmentRequest $request
     * @return JsonResponse
     */
    public function store(AssignmentRequest $request): JsonResponse
    {
        $assignment = $this->assignmentService->createAssignment($request->validated());

        return response()->success(
            new AssignmentResource($assignment),
            'Assignment created successfully',
            201
        );
    }

    /**
     * Display the specified assignment.
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

        $assignment = $this->assignmentService->getAssignmentById($id, $relations);

        if (!$assignment) {
            return response()->error('Assignment not found', null, 404);
        }

        return response()->success(
            new AssignmentResource($assignment),
            'Assignment retrieved successfully'
        );
    }

    /**
     * Update the specified assignment in storage.
     *
     * @param AssignmentRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(AssignmentRequest $request, int $id): JsonResponse
    {
        $assignment = $this->assignmentService->updateAssignment($id, $request->validated());

        if (!$assignment) {
            return response()->error('Assignment not found', null, 404);
        }

        return response()->success(
            new AssignmentResource($assignment),
            'Assignment updated successfully'
        );
    }

    /**
     * Remove the specified assignment from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->assignmentService->deleteAssignment($id);

        if (!$deleted) {
            return response()->error('Assignment not found', null, 404);
        }

        return response()->success(
            null,
            'Assignment deleted successfully'
        );
    }

    /**
     * Get assignments by course.
     *
     * @param Request $request
     * @param int $courseId
     * @return JsonResponse
     */
    public function getByCourse(Request $request, int $courseId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $assignments = $this->assignmentService->getAssignmentsByCourse($courseId, $perPage);

        return response()->success(
            AssignmentResource::collection($assignments),
            'Assignments retrieved successfully'
        );
    }

    /**
     * Get assignments by semester.
     *
     * @param Request $request
     * @param int $semesterId
     * @return JsonResponse
     */
    public function getBySemester(Request $request, int $semesterId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $assignments = $this->assignmentService->getAssignmentsBySemester($semesterId, $perPage);

        return response()->success(
            AssignmentResource::collection($assignments),
            'Assignments retrieved successfully'
        );
    }

    /**
     * Get assignments by staff.
     *
     * @param Request $request
     * @param int $staffId
     * @return JsonResponse
     */
    public function getByStaff(Request $request, int $staffId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $assignments = $this->assignmentService->getAssignmentsByStaff($staffId, $perPage);

        return response()->success(
            AssignmentResource::collection($assignments),
            'Assignments retrieved successfully'
        );
    }

    /**
     * Get upcoming assignments.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUpcoming(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $assignments = $this->assignmentService->getUpcomingAssignments($perPage);

        return response()->success(
            AssignmentResource::collection($assignments),
            'Upcoming assignments retrieved successfully'
        );
    }

    /**
     * Submit an assignment by a student.
     *
     * @param StudentAssignmentRequest $request
     * @return JsonResponse
     */
    public function submitAssignment(StudentAssignmentRequest $request): JsonResponse
    {
        $studentAssignment = $this->assignmentService->submitAssignment($request->validated());

        return response()->success(
            new StudentAssignmentResource($studentAssignment),
            'Assignment submitted successfully',
            201
        );
    }

    /**
     * Grade a student's assignment submission.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function gradeAssignment(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'score' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $studentAssignment = $this->assignmentService->gradeAssignment(
            $id,
            $request->score,
            $request->remarks
        );

        if (!$studentAssignment) {
            return response()->error('Student assignment not found', null, 404);
        }

        return response()->success(
            new StudentAssignmentResource($studentAssignment),
            'Assignment graded successfully'
        );
    }

    /**
     * Get student assignments by assignment.
     *
     * @param Request $request
     * @param int $assignmentId
     * @return JsonResponse
     */
    public function getStudentAssignmentsByAssignment(Request $request, int $assignmentId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $studentAssignments = $this->assignmentService->getStudentAssignmentsByAssignment($assignmentId, $perPage);

        return response()->success(
            StudentAssignmentResource::collection($studentAssignments),
            'Student assignments retrieved successfully'
        );
    }

    /**
     * Get student assignments by student.
     *
     * @param Request $request
     * @param int $studentId
     * @return JsonResponse
     */
    public function getStudentAssignmentsByStudent(Request $request, int $studentId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $studentAssignments = $this->assignmentService->getStudentAssignmentsByStudent($studentId, $perPage);

        return response()->success(
            StudentAssignmentResource::collection($studentAssignments),
            'Student assignments retrieved successfully'
        );
    }
}
