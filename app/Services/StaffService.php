<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class StaffService
{
    /**
     * Get all staff with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllStaff(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Staff::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get a staff by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Staff|null
     */
    public function getStaffById(int $id, array $relations = []): ?Staff
    {
        $query = Staff::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new staff with user account.
     *
     * @param array $data
     * @return Staff
     * @throws Exception|Throwable
     */
    public function createStaff(array $data): Staff
    {
        return DB::transaction(function () use ($data) {
            // Create user account
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'] ?? null,
                'address' => $data['address'] ?? null,
            ];

            $user = User::query()->create($userData);

            // Create staff record
            $staffData = [
                'user_id' => $user->id,
                'staff_id' => $data['staff_id'],
                'department_id' => $data['department_id'],
                'position' => $data['position'] ?? null,
                'joining_date' => $data['joining_date'] ?? null,
            ];

            return Staff::query()->create($staffData);
        });
    }

    /**
     * Update an existing staff.
     *
     * @param int $id
     * @param array $data
     * @return Staff|null
     * @throws Exception|Throwable
     */
    public function updateStaff(int $id, array $data): ?Staff
    {
        $staff = Staff::query()->find($id);

        if (!$staff) {
            return null;
        }

        return DB::transaction(function () use ($staff, $data) {
            // Update user data if provided
            if (isset($data['name']) || isset($data['email']) || isset($data['password']) ||
                isset($data['phone']) || isset($data['gender']) || isset($data['address'])) {

                $userData = [];

                if (isset($data['name'])) $userData['name'] = $data['name'];
                if (isset($data['email'])) $userData['email'] = $data['email'];
                if (isset($data['password'])) $userData['password'] = Hash::make($data['password']);
                if (isset($data['phone'])) $userData['phone'] = $data['phone'];
                if (isset($data['gender'])) $userData['gender'] = $data['gender'];
                if (isset($data['address'])) $userData['address'] = $data['address'];

                $staff->user->update($userData);
            }

            // Update staff data
            $staffData = [];

            if (isset($data['staff_id'])) $staffData['staff_id'] = $data['staff_id'];
            if (isset($data['department_id'])) $staffData['department_id'] = $data['department_id'];
            if (isset($data['position'])) $staffData['position'] = $data['position'];
            if (isset($data['joining_date'])) $staffData['joining_date'] = $data['joining_date'];

            $staff->update($staffData);

            return $staff;
        });
    }

    /**
     * Delete a staff.
     *
     * @param int $id
     * @return bool
     * @throws Exception|Throwable
     */
    public function deleteStaff(int $id): bool
    {
        $staff = Staff::query()->find($id);

        if (!$staff) {
            return false;
        }

        return DB::transaction(function () use ($staff) {
            // Delete the staff record
            $staff->delete();

            // Delete the associated user account
            $staff->user->delete();

            return true;
        });
    }

    /**
     * Get staff by department.
     *
     * @param int $departmentId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getStaffByDepartment(int $departmentId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Staff::query()->where('department_id', $departmentId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Search staff by name, email, or staff ID.
     *
     * @param string $term
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function searchStaff(string $term, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Staff::query()->whereHas('user', function ($query) use ($term) {
            $query->whereLike('name', "%$term%")
                ->orWhereLike('email', "%$term%");
        })->orWhereLike('staff_id', "%$term%");

        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}
