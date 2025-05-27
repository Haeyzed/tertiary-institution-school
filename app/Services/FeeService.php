<?php

namespace App\Services;

use App\Models\Fee;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FeeService
{
    /**
     * Get all fees with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllFees(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Fee::query();

        if (!empty($relations)) {
            $query->with($relations);
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
     * Delete a fee.
     *
     * @param int $id
     * @return bool
     */
    public function deleteFee(int $id): bool
    {
        $fee = Fee::query()->find($id);

        if (!$fee) {
            return false;
        }

        return $fee->delete();
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
