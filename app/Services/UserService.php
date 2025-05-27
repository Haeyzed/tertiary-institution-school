<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    /**
     * Get all users with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllUsers(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = User::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get a user by ID.
     *
     * @param int $id
     * @param array $relations
     * @return User|null
     */
    public function getUserById(int $id, array $relations = []): ?User
    {
        $query = User::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return User::query()->create($data);
    }

    /**
     * Update an existing user.
     *
     * @param int $id
     * @param array $data
     * @return User|null
     */
    public function updateUser(int $id, array $data): ?User
    {
        $user = User::query()->find($id);

        if (!$user) {
            return null;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $user;
    }

    /**
     * Delete a user.
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        $user = User::query()->find($id);

        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    /**
     * Search users by name or email.
     *
     * @param string $term
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function searchUsers(string $term, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = User::query()
            ->whereLike('name', "%$term%")
            ->orWhereLike('email', "%$term%");

        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}
