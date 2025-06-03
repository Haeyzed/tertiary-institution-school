<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\StudentAssignment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AssignmentService
{
    /**
     * Get all assignments with optional pagination.
     *
     * @param string $term
     * @param int|null $perPage
     * @param array $relations
     * @param bool|null $onlyDeleted
     * @return Collection|LengthAwarePaginator
     */
    public function getAllAssignments(string $term, ?int $perPage = null, array $relations = [], ?bool $onlyDeleted = null): Collection|LengthAwarePaginator
    {
        $query = Assignment::query()
            ->where(function ($q) use ($term) {
                $q->whereLike('title', "%$term%")
                    ->orwhereLike('description', "%$term%");
            });;

        if (!empty($relations)) {
            $query->with($relations);
        }

        if ($onlyDeleted === true) {
            $query->onlyTrashed();
        } elseif ($onlyDeleted === false) {
            $query->withoutTrashed();
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get an assignment by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Assignment|null
     */
    public function getAssignmentById(int $id, array $relations = []): ?Assignment
    {
        $query = Assignment::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new assignment.
     *
     * @param array $data
     * @return Assignment
     */
    public function createAssignment(array $data): Assignment
    {
        return Assignment::query()->create($data);
    }

    /**
     * Update an existing assignment.
     *
     * @param int $id
     * @param array $data
     * @return Assignment|null
     */
    public function updateAssignment(int $id, array $data): ?Assignment
    {
        $assignment = Assignment::query()->find($id);

        if (!$assignment) {
            return null;
        }

        $assignment->update($data);

        return $assignment;
    }

    /**
     * Delete or force delete an assignment.
     *
     * @param int $id
     * @param bool $force
     * @return bool
     */
    public function deleteAssignment(int $id, bool $force = false): bool
    {
        $assignment = Assignment::withTrashed()->find($id);

        if (!$assignment) {
            return false;
        }

        return $force ? $assignment->forceDelete() : $assignment->delete();
    }

    /**
     * Restore a delete assignment.
     *
     * @param int $id
     * @return Assignment|null
     */
    public function restoreAssignment(int $id): ?Assignment
    {
        $assignment = Assignment::onlyTrashed()->find($id);

        if (!$assignment) {
            return null;
        }

        $assignment->restore();

        return $assignment->fresh();
    }

    /**
     * Get assignments by course.
     *
     * @param int $courseId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getAssignmentsByCourse(int $courseId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Assignment::query()->where('course_id', $courseId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get assignments by semester.
     *
     * @param int $semesterId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getAssignmentsBySemester(int $semesterId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Assignment::query()->where('semester_id', $semesterId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get assignments by staff.
     *
     * @param int $staffId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getAssignmentsByStaff(int $staffId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Assignment::query()->where('staff_id', $staffId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get upcoming assignments (due date is in the future).
     *
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getUpcomingAssignments(?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Assignment::query()->where('due_date', '>=', now())
            ->orderBy('due_date', 'asc');

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Submit an assignment by a student.
     *
     * @param array $data
     * @return StudentAssignment
     */
    public function submitAssignment(array $data): StudentAssignment
    {
        return StudentAssignment::query()->create($data);
    }

    /**
     * Grade a student's assignment submission.
     *
     * @param int $studentAssignmentId
     * @param float $score
     * @param string|null $remarks
     * @return StudentAssignment|null
     */
    public function gradeAssignment(int $studentAssignmentId, float $score, ?string $remarks = null): ?StudentAssignment
    {
        $studentAssignment = StudentAssignment::query()->find($studentAssignmentId);

        if (!$studentAssignment) {
            return null;
        }

        $studentAssignment->update([
            'score' => $score,
            'remarks' => $remarks,
        ]);

        return $studentAssignment;
    }

    /**
     * Get student assignments by assignment.
     *
     * @param int $assignmentId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getStudentAssignmentsByAssignment(int $assignmentId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = StudentAssignment::query()->where('assignment_id', $assignmentId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get student assignments by student.
     *
     * @param int $studentId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getStudentAssignmentsByStudent(int $studentId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = StudentAssignment::query()->where('student_id', $studentId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}
