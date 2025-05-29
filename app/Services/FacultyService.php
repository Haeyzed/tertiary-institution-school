<?php

namespace App\Services;

use App\Models\Faculty;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FacultyService
{
    /**
     * Get all faculties with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllFaculties(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Faculty::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get a faculty by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Faculty|null
     */
    public function getFacultyById(int $id, array $relations = []): ?Faculty
    {
        $query = Faculty::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new faculty.
     *
     * @param array $data
     * @return Faculty
     */
    public function createFaculty(array $data): Faculty
    {
        return Faculty::query()->create($data);
    }

    /**
     * Update an existing faculty.
     *
     * @param int $id
     * @param array $data
     * @return Faculty|null
     */
    public function updateFaculty(int $id, array $data): ?Faculty
    {
        $faculty = Faculty::query()->find($id);

        if (!$faculty) {
            return null;
        }

        $faculty->update($data);

        return $faculty;
    }

    /**
     * Delete a faculty.
     *
     * @param int $id
     * @return bool
     */
    public function deleteFaculty(int $id): bool
    {
        $faculty = Faculty::query()->find($id);

        if (!$faculty) {
            return false;
        }

        return $faculty->delete();
    }

    /**
     * Search faculties by name or code.
     *
     * @param string $term
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function searchFaculties(string $term, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Faculty::query()
            ->whereLike('name', "%$term%")
            ->orWhereLike('code', "%$term%");

        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}
