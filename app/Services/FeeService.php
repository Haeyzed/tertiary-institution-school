<?php

namespace App\Services;

use App\Models\Fee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FeeService
{
    /**
     * Get all fees with optional pagination.
     *
     * @param string $term
     * @param int|null $perPage
     * @param array $relations
     * @param bool|null $onlyDeleted
     * @return Collection|LengthAwarePaginator
     */
    public function getAllFees(string $term, ?int $perPage = null, array $relations = [], ?bool $onlyDeleted = null): Collection|LengthAwarePaginator
    {
        $query = Fee::query()
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
     * Get a fee by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Fee|null
     */
    public function getFeeById(int $id, array $relations = []): ?Fee
    {
        $query = Fee::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new fee.
     *
     * @param array $data
     * @return Fee
     */
    public function createFee(array $data): Fee
    {
        return Fee::query()->create($data);
    }

    /**
     * Update an existing fee.
     *
     * @param int $id
     * @param array $data
     * @return Fee|null
     */
    public function updateFee(int $id, array $data): ?Fee
    {
        $fee = Fee::query()->find($id);

        if (!$fee) {
            return null;
        }

        $fee->update($data);

        return $fee;
    }

    /**
     * Delete or force delete a fee.
     *
     * @param int $id
     * @param bool $force
     * @return bool
     */
    public function deleteFee(int $id, bool $force = false): bool
    {
        $fee = Fee::withTrashed()->find($id);

        if (!$fee) {
            return false;
        }

        return $force ? $fee->forceDelete() : $fee->delete();
    }

    /**
     * Restore a delete fee.
     *
     * @param int $id
     * @return Fee|null
     */
    public function restoreFee(int $id): ?Fee
    {
        $fee = Fee::onlyTrashed()->find($id);

        if (!$fee) {
            return null;
        }

        $fee->restore();

        return $fee->fresh();
    }

    /**
     * Get fees by program.
     *
     * @param int $programId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getFeesByProgram(int $programId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Fee::query()->where('program_id', $programId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get fees by semester.
     *
     * @param int $semesterId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getFeesBySemester(int $semesterId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Fee::query()->where('semester_id', $semesterId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get current semester fees.
     *
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getCurrentSemesterFees(?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Fee::query()->whereHas('semester.academicSession', function ($query) {
            $query->where('is_current', true);
        });

        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}
