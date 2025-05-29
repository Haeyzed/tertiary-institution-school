<?php

namespace App\Services;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class GradeService
{
    /**
     * Get all grades with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllGrades(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Grade::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get a grade by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Grade|null
     */
    public function getGradeById(int $id, array $relations = []): ?Grade
    {
        $query = Grade::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new grade.
     *
     * @param array $data
     * @return Grade
     */
    public function createGrade(array $data): Grade
    {
        return Grade::query()->create($data);
    }

    /**
     * Update an existing grade.
     *
     * @param int $id
     * @param array $data
     * @return Grade|null
     */
    public function updateGrade(int $id, array $data): ?Grade
    {
        $grade = Grade::query()->find($id);

        if (!$grade) {
            return null;
        }

        $grade->update($data);

        return $grade;
    }

    /**
     * Delete a grade.
     *
     * @param int $id
     * @return bool
     */
    public function deleteGrade(int $id): bool
    {
        $grade = Grade::query()->find($id);

        if (!$grade) {
            return false;
        }

        return $grade->delete();
    }

    /**
     * Get grade by score.
     *
     * @param float $score
     * @return Grade|null
     */
    public function getGradeByScore(float $score): ?Grade
    {
        return Grade::query()->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();
    }
}
