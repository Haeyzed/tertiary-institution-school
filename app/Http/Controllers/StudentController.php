<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Http\Resources\StudentResource;
use App\Services\StudentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class StudentController extends Controller
{
    /**
     * The student service instance.
     *
     * @var StudentService
     */
    protected StudentService $studentService;

    /**
     * Create a new controller instance.
     *
     * @param StudentService $studentService
     * @return void
     */
    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    /**
     * Display a listing of the students.
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

        $students = $this->studentService->getAllStudents($term, $perPage, $relations, $deleted);

        return response()->success(
            StudentResource::collection($students),
            'Students retrieved successfully'
        );
    }

    /**
     * Store a newly created student in storage.
     *
     * @param StudentRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(StudentRequest $request): JsonResponse
    {
        try {
            $student = $this->studentService->createStudent($request->validated());

            return response()->success(
                new StudentResource($student),
                'Student created successfully',
                201
            );
        } catch (Exception $e) {
            return response()->error(
                'Failed to create student',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Display the specified student.
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

        $student = $this->studentService->getStudentById($id, $relations);

        if (!$student) {
            return response()->error('Student not found', null, 404);
        }

        return response()->success(
            new StudentResource($student),
            'Student retrieved successfully'
        );
    }

    /**
     * Update the specified student in storage.
     *
     * @param StudentRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(StudentRequest $request, int $id): JsonResponse
    {
        try {
            $student = $this->studentService->updateStudent($id, $request->validated());

            if (!$student) {
                return response()->error('Student not found', null, 404);
            }

            return response()->success(
                new StudentResource($student),
                'Student updated successfully'
            );
        } catch (Exception $e) {
            return response()->error(
                'Failed to update student',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Remove the specified student from storage.
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
            $deleted = $this->studentService->deleteStudent($id, $force);

            if (!$deleted) {
                return response()->error('Student not found', null, 404);
            }

            return response()->success(
                null,
                'Student deleted successfully'
            );
        } catch (Exception $e) {
            return response()->error(
                'Failed to delete student',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Restore a soft-deleted student.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $restored = $this->studentService->restoreStudent($id);

        if (!$restored) {
            return response()->error('Student not found or not deleted', null, 404);
        }

        return response()->success(
            new StudentResource($restored),
            'Student restored successfully'
        );
    }

    /**
     * Get students by program.
     *
     * @param Request $request
     * @param int $programId
     * @return JsonResponse
     */
    public function getByProgram(Request $request, int $programId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $students = $this->studentService->getStudentsByProgram($programId, $perPage);

        return response()->success(
            StudentResource::collection($students),
            'Students retrieved successfully'
        );
    }

    /**
     * Enroll a student in a course.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function enrollInCourse(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'course_id' => 'required|integer|exists:courses,id',
            'semester_id' => 'required|integer|exists:semesters,id',
        ]);

        $enrolled = $this->studentService->enrollStudentInCourse(
            $request->student_id,
            $request->course_id,
            $request->semester_id
        );

        if (!$enrolled) {
            return response()->error('Failed to enroll student in course', null, 400);
        }

        return response()->success(
            null,
            'Student enrolled in course successfully'
        );
    }

    /**
     * Unenroll a student from a course.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unenrollFromCourse(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'course_id' => 'required|integer|exists:courses,id',
            'semester_id' => 'required|integer|exists:semesters,id',
        ]);

        $unenrolled = $this->studentService->unenrollStudentFromCourse(
            $request->student_id,
            $request->course_id,
            $request->semester_id
        );

        if (!$unenrolled) {
            return response()->error('Failed to unenroll student from course', null, 400);
        }

        return response()->success(
            null,
            'Student unenrolled from course successfully'
        );
    }
}
