<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentService
{
    /**
     * Get all departments with optional pagination.
     *
     * @param string $term
     * @param int|null $perPage
     * @param array $relations
     * @param bool|null $onlyDeleted
     * @return Collection|LengthAwarePaginator
     */
    public function getAllDepartments(string $term, ?int $perPage = null, array $relations = [], ?bool $onlyDeleted = null): Collection|LengthAwarePaginator
    {
        $query = Department::query()
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
     * Get a department by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Department|null
     */
    public function getDepartmentById(int $id, array $relations = []): ?Department
    {
        $query = Department::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new department.
     *
     * @param array $data
     * @return Department
     */
    public function createDepartment(array $data): Department
    {
        return Department::query()->create($data);
    }

    /**
     * Update an existing department.
     *
     * @param int $id
     * @param array $data
     * @return Department|null
     */
    public function updateDepartment(int $id, array $data): ?Department
    {
        $department = Department::query()->find($id);

        if (!$department) {
            return null;
        }

        $department->update($data);

        return $department;
    }

    /**
     * Delete or force delete a department.
     *
     * @param int $id
     * @param bool $force
     * @return bool
     */
    public function deleteDepartment(int $id, bool $force = false): bool
    {
        $department = Department::withTrashed()->find($id);

        if (!$department) {
            return false;
        }

        return $force ? $department->forceDelete() : $department->delete();
    }

    /**
     * Restore a delete department.
     *
     * @param int $id
     * @return Department|null
     */
    public function restoreDepartment(int $id): ?Department
    {
        $department = Department::onlyTrashed()->find($id);

        if (!$department) {
            return null;
        }

        $department->restore();

        return $department->fresh();
    }

    /**
     * Get departments by faculty.
     *
     * @param int $facultyId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getDepartmentsByFaculty(int $facultyId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Department::query()->where('faculty_id', $facultyId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}
