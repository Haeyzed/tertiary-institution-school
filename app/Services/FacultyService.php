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
     * @param string $term
     * @param int|null $perPage
     * @param array $relations
     * @param bool|null $onlyDeleted
     * @return Collection|LengthAwarePaginator
     */
    public function getAllFaculties(string $term, ?int $perPage = null, array $relations = [], ?bool $onlyDeleted = null): Collection|LengthAwarePaginator
    {
        $query = Faculty::query()
            ->where(function ($q) use ($term) {
                $q->whereLike('name', "%$term%")
                    ->orwhereLike('code', "%$term%");
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
     * Delete or force delete a faculty.
     *
     * @param int $id
     * @param bool $force
     * @return bool
     */
    public function deleteFaculty(int $id, bool $force = false): bool
    {
        $faculty = Faculty::withTrashed()->find($id);

        if (!$faculty) {
            return false;
        }

        return $force ? $faculty->forceDelete() : $faculty->delete();
    }

    /**
     * Restore a delete faculty.
     *
     * @param int $id
     * @return Faculty|null
     */
    public function restoreFaculty(int $id): ?Faculty
    {
        $faculty = Faculty::onlyTrashed()->find($id);

        if (!$faculty) {
            return null;
        }

        $faculty->restore();

        return $faculty->fresh();
    }
}
