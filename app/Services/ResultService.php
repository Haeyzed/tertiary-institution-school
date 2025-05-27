<?php

namespace App\Services;

use App\Models\Result;
use App\Models\Grade;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ResultService
{
    /**
     * Get all results with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllResults(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Result::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get a result by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Result|null
     */
    public function getResultById(int $id, array $relations = []): ?Result
    {
        $query = Result::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new result.
     *
     * @param array $data
     * @return Result
     */
    public function createResult(array $data): Result
    {
        // Determine grade based on score
        if (!isset($data['grade_id']) && isset($data['score'])) {
            $grade = $this->determineGrade($data['score']);
            if ($grade) {
                $data['grade_id'] = $grade->id;
            }
        }

        return Result::query()->create($data);
    }

    /**
     * Update an existing result.
     *
     * @param int $id
     * @param array $data
     * @return Result|null
     */
    public function updateResult(int $id, array $data): ?Result
    {
        $result = Result::query()->find($id);

        if (!$result) {
            return null;
        }

        // Determine grade based on score if score is updated
        if (isset($data['score']) && $data['score'] != $result->score) {
            $grade = $this->determineGrade($data['score']);
            if ($grade) {
                $data['grade_id'] = $grade->id;
            }
        }

        $result->update($data);

        return $result;
    }

    /**
     * Delete a result.
     *
     * @param int $id
     * @return bool
     */
    public function deleteResult(int $id): bool
    {
        $result = Result::query()->find($id);

        if (!$result) {
            return false;
        }

        return $result->delete();
    }

    /**
     * Get results by student.
     *
     * @param int $studentId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getResultsByStudent(int $studentId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Result::query()->where('student_id', $studentId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get results by course.
     *
     * @param int $courseId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getResultsByCourse(int $courseId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Result::query()->where('course_id', $courseId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get results by exam.
     *
     * @param int $examId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getResultsByExam(int $examId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Result::query()->where('exam_id', $examId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get results by semester.
     *
     * @param int $semesterId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getResultsBySemester(int $semesterId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Result::query()->where('semester_id', $semesterId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get student's semester GPA.
     *
     * @param int $studentId
     * @param int $semesterId
     * @return float|null
     */
    public function getStudentSemesterGPA(int $studentId, int $semesterId): float|int|null
    {
        $results = Result::query()->where([
            'student_id' => $studentId,
            'semester_id' => $semesterId,
        ])->with(['course', 'grade'])->get();

        if ($results->isEmpty()) {
            return null;
        }

        $totalPoints = 0;
        $totalCreditHours = 0;

        foreach ($results as $result) {
            // Assuming grade has a point value (e.g., A = 4.0, B = 3.0, etc.)
            // You might need to adjust this based on your grading system
            $gradePoint = $this->getGradePoint($result->grade);
            $creditHours = $result->course->credit_hours;

            $totalPoints += ($gradePoint * $creditHours);
            $totalCreditHours += $creditHours;
        }

        if ($totalCreditHours == 0) {
            return 0;
        }

        return round($totalPoints / $totalCreditHours, 2);
    }

    /**
     * Get student's cumulative GPA.
     *
     * @param int $studentId
     * @return float|null
     */
    public function getStudentCumulativeGPA(int $studentId): float|int|null
    {
        $results = Result::query()->where('student_id', $studentId)
            ->with(['course', 'grade'])
            ->get();

        if ($results->isEmpty()) {
            return null;
        }

        $totalPoints = 0;
        $totalCreditHours = 0;

        foreach ($results as $result) {
            $gradePoint = $this->getGradePoint($result->grade);
            $creditHours = $result->course->credit_hours;

            $totalPoints += ($gradePoint * $creditHours);
            $totalCreditHours += $creditHours;
        }

        if ($totalCreditHours == 0) {
            return 0;
        }

        return round($totalPoints / $totalCreditHours, 2);
    }

    /**
     * Determine grade based on score.
     *
     * @param float $score
     * @return Grade|null
     */
    private function determineGrade(float $score): ?Grade
    {
        return Grade::query()->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();
    }

    /**
     * Get grade point value.
     *
     * @param Grade|null $grade
     * @return float
     */
    private function getGradePoint(?Grade $grade): float|int
    {
        if (!$grade) {
            return 0;
        }

        // This is a simplified example. You should adjust based on your grading system
        // For example, you might have a 'point' column in your grades table
        return match ($grade->grade) {
            'A' => 4.0,
            'B' => 3.0,
            'C' => 2.0,
            'D' => 1.0,
            'F' => 0.0,
            default => 0.0,
        };
    }
}
