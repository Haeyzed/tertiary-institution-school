<?php

namespace App\Services;

use App\Models\Parents;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class ParentService
{
    /**
     * Get all parents with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllParents(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Parents::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get a parent by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Parents|null
     */
    public function getParentById(int $id, array $relations = []): ?Parents
    {
        $query = Parents::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new parent with user account.
     *
     * @param array $data
     * @return Parents
     * @throws Exception|Throwable
     */
    public function createParent(array $data): Parents
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

            // Create parent record
            $parentData = [
                'user_id' => $user->id,
                'occupation' => $data['occupation'] ?? null,
                'relationship' => $data['relationship'] ?? null,
            ];

            return Parents::query()->create($parentData);
        });
    }

    /**
     * Update an existing parent.
     *
     * @param int $id
     * @param array $data
     * @return Parents|null
     * @throws Exception|Throwable
     */
    public function updateParent(int $id, array $data): ?Parents
    {
        $parent = Parents::query()->find($id);

        if (!$parent) {
            return null;
        }

        return DB::transaction(function () use ($parent, $data) {
            // Update user data if provided
            $userData = [];

            if (isset($data['name'])) $userData['name'] = $data['name'];
            if (isset($data['email'])) $userData['email'] = $data['email'];
            if (isset($data['password'])) $userData['password'] = Hash::make($data['password']);
            if (isset($data['phone'])) $userData['phone'] = $data['phone'];
            if (isset($data['gender'])) $userData['gender'] = $data['gender'];
            if (isset($data['address'])) $userData['address'] = $data['address'];

            if (!empty($userData)) {
                $parent->user->update($userData);
            }

            // Update parent data
            $parentData = [];

            if (isset($data['occupation'])) $parentData['occupation'] = $data['occupation'];
            if (isset($data['relationship'])) $parentData['relationship'] = $data['relationship'];

            $parent->update($parentData);

            return $parent;
        });
    }

    /**
     * Delete a parent.
     *
     * @param int $id
     * @return bool
     * @throws Exception|Throwable
     */
    public function deleteParent(int $id): bool
    {
        $parent = Parents::query()->find($id);

        if (!$parent) {
            return false;
        }

        return DB::transaction(function () use ($parent) {
            $parent->delete();
            $parent->user->delete();
            return true;
        });
    }

    /**
     * Search parents by name or email.
     *
     * @param string $term
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function searchParents(string $term, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Parents::query()->whereHas('user', function ($query) use ($term) {
            $query->whereLike('name', "%$term%")
                ->orWhereLike('email', "%$term%");
        });

        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}
