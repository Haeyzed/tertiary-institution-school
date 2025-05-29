<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Http\Resources\CourseResource;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * The course service instance.
     *
     * @var CourseService
     */
    protected CourseService $courseService;

    /**
     * Create a new controller instance.
     *
     * @param CourseService $courseService
     * @return void
     */
    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Display a listing of the courses.
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

        $courses = $this->courseService->getAllCourses($perPage, $relations);

        return response()->success(
            CourseResource::collection($courses),
            'Courses retrieved successfully'
        );
    }

    /**
     * Store a newly created course in storage.
     *
     * @param CourseRequest $request
     * @return JsonResponse
     */
    public function store(CourseRequest $request): JsonResponse
    {
        $course = $this->courseService->createCourse($request->validated());

        return response()->success(
            new CourseResource($course),
            'Course created successfully',
            201
        );
    }

    /**
     * Display the specified course.
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

        $course = $this->courseService->getCourseById($id, $relations);

        if (!$course) {
            return response()->error('Course not found', null, 404);
        }

        return response()->success(
            new CourseResource($course),
            'Course retrieved successfully'
        );
    }

    /**
     * Update the specified course in storage.
     *
     * @param CourseRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(CourseRequest $request, int $id): JsonResponse
    {
        $course = $this->courseService->updateCourse($id, $request->validated());

        if (!$course) {
            return response()->error('Course not found', null, 404);
        }

        return response()->success(
            new CourseResource($course),
            'Course updated successfully'
        );
    }

    /**
     * Remove the specified course from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->courseService->deleteCourse($id);

        if (!$deleted) {
            return response()->error('Course not found', null, 404);
        }

        return response()->success(
            null,
            'Course deleted successfully'
        );
    }

    /**
     * Get courses by department.
     *
     * @param Request $request
     * @param int $departmentId
     * @return JsonResponse
     */
    public function getByDepartment(Request $request, int $departmentId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $courses = $this->courseService->getCoursesByDepartment($departmentId, $perPage);

        return response()->success(
            CourseResource::collection($courses),
            'Courses retrieved successfully'
        );
    }

    /**
     * Get courses by semester.
     *
     * @param Request $request
     * @param int $semesterId
     * @return JsonResponse
     */
    public function getBySemester(Request $request, int $semesterId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $courses = $this->courseService->getCoursesBySemester($semesterId, $perPage);

        return response()->success(
            CourseResource::collection($courses),
            'Courses retrieved successfully'
        );
    }

    /**
     * Assign a course to a semester with a staff member.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function assignToSemester(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'staff_id' => 'required|integer|exists:staff,id',
        ]);

        $assigned = $this->courseService->assignCourseToSemester(
            $request->course_id,
            $request->semester_id,
            $request->staff_id
        );

        if (!$assigned) {
            return response()->error('Failed to assign course to semester', null, 400);
        }

        return response()->success(
            null,
            'Course assigned to semester successfully'
        );
    }

    /**
     * Remove a course from a semester.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFromSemester(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'semester_id' => 'required|integer|exists:semesters,id',
        ]);

        $removed = $this->courseService->removeCourseFromSemester(
            $request->course_id,
            $request->semester_id
        );

        if (!$removed) {
            return response()->error('Failed to remove course from semester', null, 400);
        }

        return response()->success(
            null,
            'Course removed from semester successfully'
        );
    }
}
