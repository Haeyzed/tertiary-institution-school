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
     * @param string $term
     * @param int|null $perPage
     * @param array $relations
     * @param bool|null $onlyDeleted
     * @return Collection|LengthAwarePaginator
     */
    public function getAllPrograms(string $term, ?int $perPage = null, array $relations = [], ?bool $onlyDeleted = null): Collection|LengthAwarePaginator
    {
        $query = Program::query()
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
     * Delete or force delete a program.
     *
     * @param int $id
     * @param bool $force
     * @return bool
     */
    public function deleteProgram(int $id, bool $force = false): bool
    {
        $program = Program::withTrashed()->find($id);

        if (!$program) {
            return false;
        }

        return $force ? $program->forceDelete() : $program->delete();
    }

    /**
     * Restore a delete program.
     *
     * @param int $id
     * @return Program|null
     */
    public function restoreProgram(int $id): ?Program
    {
        $program = Program::onlyTrashed()->find($id);

        if (!$program) {
            return null;
        }

        $program->restore();

        return $program->fresh();
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
