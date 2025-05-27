<?php

namespace App\Services;

use App\Models\Semester;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SemesterService
{
    /**
     * Get all semesters with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllSemesters(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Semester::query();

        if (!empty($relations)) {
            $query->with($relations);
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
     * Delete a semester.
     *
     * @param int $id
     * @return bool
     */
    public function deleteSemester(int $id): bool
    {
        $semester = Semester::query()->find($id);

        if (!$semester) {
            return false;
        }

        return $semester->delete();
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
