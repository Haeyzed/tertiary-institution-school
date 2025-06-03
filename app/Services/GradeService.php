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
     * @param string $term
     * @param int|null $perPage
     * @param array $relations
     * @param bool|null $onlyDeleted
     * @return Collection|LengthAwarePaginator
     */
    public function getAllGrades(string $term, ?int $perPage = null, array $relations = [], ?bool $onlyDeleted = null): Collection|LengthAwarePaginator
    {
        $query = Grade::query()
            ->where(function ($q) use ($term) {
                $q->whereLike('grade', "%$term%");
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
     * Delete or force delete a grade.
     *
     * @param int $id
     * @param bool $force
     * @return bool
     */
    public function deleteGrade(int $id, bool $force = false): bool
    {
        $grade = Grade::withTrashed()->find($id);

        if (!$grade) {
            return false;
        }

        return $force ? $grade->forceDelete() : $grade->delete();
    }

    /**
     * Restore a delete grade.
     *
     * @param int $id
     * @return Grade|null
     */
    public function restoreGrade(int $id): ?Grade
    {
        $grade = Grade::onlyTrashed()->find($id);

        if (!$grade) {
            return null;
        }

        $grade->restore();

        return $grade->fresh();
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
