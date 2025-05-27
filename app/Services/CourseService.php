<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseSemester;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CourseService
{
    /**
     * Get all courses with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllCourses(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Course::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get a course by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Course|null
     */
    public function getCourseById(int $id, array $relations = []): ?Course
    {
        $query = Course::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new course.
     *
     * @param array $data
     * @return Course
     */
    public function createCourse(array $data): Course
    {
        return Course::query()->create($data);
    }

    /**
     * Update an existing course.
     *
     * @param int $id
     * @param array $data
     * @return Course|null
     */
    public function updateCourse(int $id, array $data): ?Course
    {
        $course = Course::query()->find($id);

        if (!$course) {
            return null;
        }

        $course->update($data);

        return $course;
    }

    /**
     * Delete a course.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCourse(int $id): bool
    {
        $course = Course::query()->find($id);

        if (!$course) {
            return false;
        }

        return $course->delete();
    }

    /**
     * Get courses by department.
     *
     * @param int $departmentId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getCoursesByDepartment(int $departmentId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Course::query()->where('department_id', $departmentId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Assign a course to a semester with a staff member.
     *
     * @param int $courseId
     * @param int $semesterId
     * @param int $staffId
     * @return bool
     */
    public function assignCourseToSemester(int $courseId, int $semesterId, int $staffId): bool
    {
        $course = Course::query()->find($courseId);

        if (!$course) {
            return false;
        }

        // Check if already assigned
        $exists = CourseSemester::query()->where([
            'course_id' => $courseId,
            'semester_id' => $semesterId,
        ])->exists();

        if ($exists) {
            // Update the staff
            CourseSemester::query()->where([
                'course_id' => $courseId,
                'semester_id' => $semesterId,
            ])->update(['staff_id' => $staffId]);
        } else {
            // Create new assignment
            CourseSemester::query()->create([
                'course_id' => $courseId,
                'semester_id' => $semesterId,
                'staff_id' => $staffId,
            ]);
        }

        return true;
    }

    /**
     * Remove a course from a semester.
     *
     * @param int $courseId
     * @param int $semesterId
     * @return bool
     */
    public function removeCourseFromSemester(int $courseId, int $semesterId): bool
    {
        return CourseSemester::query()->where([
            'course_id' => $courseId,
            'semester_id' => $semesterId,
        ])->delete();
    }

    /**
     * Get courses by semester.
     *
     * @param int $semesterId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getCoursesBySemester(int $semesterId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Course::query()->whereHas('semesters', function ($query) use ($semesterId) {
            $query->where('semester_id', $semesterId);
        });

        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}
