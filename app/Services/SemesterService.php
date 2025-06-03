<?php

namespace App\Services;

use App\Models\Semester;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SemesterService
{
    /**
     * Get all semesters with optional pagination.
     *
     * @param string $term
     * @param int|null $perPage
     * @param array $relations
     * @param bool|null $onlyDeleted
     * @return Collection|LengthAwarePaginator
     */
    public function getAllSemesters(string $term, ?int $perPage = null, array $relations = [], ?bool $onlyDeleted = null): Collection|LengthAwarePaginator
    {
        $query = Semester::query()
            ->where(function ($q) use ($term) {
                $q->whereLike('name', "%$term%");
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
     * Get a semester by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Semester|null
     */
    public function getSemesterById(int $id, array $relations = []): ?Semester
    {
        $query = Semester::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new semester.
     *
     * @param array $data
     * @return Semester
     */
    public function createSemester(array $data): Semester
    {
        return Semester::query()->create($data);
    }

    /**
     * Update an existing semester.
     *
     * @param int $id
     * @param array $data
     * @return Semester|null
     */
    public function updateSemester(int $id, array $data): ?Semester
    {
        $semester = Semester::query()->find($id);

        if (!$semester) {
            return null;
        }

        $semester->update($data);

        return $semester;
    }

    /**
     * Delete or force delete a semester.
     *
     * @param int $id
     * @param bool $force
     * @return bool
     */
    public function deleteSemester(int $id, bool $force = false): bool
    {
        $semester = Semester::withTrashed()->find($id);

        if (!$semester) {
            return false;
        }

        return $force ? $semester->forceDelete() : $semester->delete();
    }

    /**
     * Restore a delete semester.
     *
     * @param int $id
     * @return Semester|null
     */
    public function restoreSemester(int $id): ?Semester
    {
        $semester = Semester::onlyTrashed()->find($id);

        if (!$semester) {
            return null;
        }

        $semester->restore();

        return $semester->fresh();
    }

    /**
     * Get semesters by academic session.
     *
     * @param int $academicSessionId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getSemestersByAcademicSession(int $academicSessionId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Semester::query()->where('academic_session_id', $academicSessionId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get current semesters (semesters in the current academic session).
     *
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getCurrentSemesters(?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Semester::query()->whereHas('academicSession', function ($query) {
            $query->where('is_current', true);
        });

        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}
