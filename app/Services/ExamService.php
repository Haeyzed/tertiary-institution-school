<?php

namespace App\Services;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ExamService
{
    /**
     * Get all exams with optional pagination.
     *
     * @param string $term
     * @param int|null $perPage
     * @param array $relations
     * @param bool|null $onlyDeleted
     * @return Collection|LengthAwarePaginator
     */
    public function getAllExams(string $term, ?int $perPage = null, array $relations = [], ?bool $onlyDeleted = null): Collection|LengthAwarePaginator
    {
        $query = Exam::query()
            ->where(function ($q) use ($term) {
                $q->whereLike('title', "%$term%");
            });

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
     * Get an exam by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Exam|null
     */
    public function getExamById(int $id, array $relations = []): ?Exam
    {
        $query = Exam::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new exam.
     *
     * @param array $data
     * @return Exam
     */
    public function createExam(array $data): Exam
    {
        return Exam::query()->create($data);
    }

    /**
     * Update an existing exam.
     *
     * @param int $id
     * @param array $data
     * @return Exam|null
     */
    public function updateExam(int $id, array $data): ?Exam
    {
        $exam = Exam::query()->find($id);

        if (!$exam) {
            return null;
        }

        $exam->update($data);

        return $exam;
    }

    /**
     * Delete or force delete an exam.
     *
     * @param int $id
     * @param bool $force
     * @return bool
     */
    public function deleteExam(int $id, bool $force = false): bool
    {
        $exam = Exam::withTrashed()->find($id);

        if (!$exam) {
            return false;
        }

        return $force ? $exam->forceDelete() : $exam->delete();
    }

    /**
     * Restore a delete exam.
     *
     * @param int $id
     * @return Exam|null
     */
    public function restoreExam(int $id): ?Exam
    {
        $exam = Exam::onlyTrashed()->find($id);

        if (!$exam) {
            return null;
        }

        $exam->restore();

        return $exam->fresh();
    }

    /**
     * Get exams by course.
     *
     * @param int $courseId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getExamsByCourse(int $courseId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Exam::query()->where('course_id', $courseId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get exams by semester.
     *
     * @param int $semesterId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getExamsBySemester(int $semesterId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Exam::query()->where('semester_id', $semesterId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get upcoming exams.
     *
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getUpcomingExams(?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Exam::query()->where('exam_date', '>=', now())
            ->orderBy('exam_date', 'asc');

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Update exam status.
     *
     * @param int $id
     * @param string $status
     * @return Exam|null
     */
    public function updateExamStatus(int $id, string $status): ?Exam
    {
        $exam = Exam::query()->find($id);

        if (!$exam) {
            return null;
        }

        $exam->update(['status' => $status]);

        return $exam;
    }
}
