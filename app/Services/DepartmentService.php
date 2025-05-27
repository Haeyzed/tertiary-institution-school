<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DepartmentService
{
    /**
     * Get all departments with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllDepartments(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Department::query();

        if (!empty($relations)) {
            $query->with($relations);
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
     * Delete a department.
     *
     * @param int $id
     * @return bool
     */
    public function deleteDepartment(int $id): bool
    {
        $department = Department::query()->find($id);

        if (!$department) {
            return false;
        }

        return $department->delete();
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

    /**
     * Search departments by name or code.
     *
     * @param string $term
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function searchDepartments(string $term, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Department::query()
            ->whereLike('name', "%$term%")
            ->orWhereLike('code', "%$term%");

        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}
