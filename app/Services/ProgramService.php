<?php

namespace App\Services;

use App\Models\Program;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProgramService
{
    /**
     * Get all programs with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllPrograms(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Program::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get a program by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Program|null
     */
    public function getProgramById(int $id, array $relations = []): ?Program
    {
        $query = Program::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new program.
     *
     * @param array $data
     * @return Program
     */
    public function createProgram(array $data): Program
    {
        return Program::query()->create($data);
    }

    /**
     * Update an existing program.
     *
     * @param int $id
     * @param array $data
     * @return Program|null
     */
    public function updateProgram(int $id, array $data): ?Program
    {
        $program = Program::query()->find($id);

        if (!$program) {
            return null;
        }

        $program->update($data);

        return $program;
    }

    /**
     * Delete a program.
     *
     * @param int $id
     * @return bool
     */
    public function deleteProgram(int $id): bool
    {
        $program = Program::query()->find($id);

        if (!$program) {
            return false;
        }

        return $program->delete();
    }

    /**
     * Get programs by department.
     *
     * @param int $departmentId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getProgramsByDepartment(int $departmentId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Program::query()->where('department_id', $departmentId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Search programs by name or code.
     *
     * @param string $term
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function searchPrograms(string $term, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Program::query()
            ->whereLike('name', "%$term%")
            ->orWhereLike('code', "%$term%");

        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}
