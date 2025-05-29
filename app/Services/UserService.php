<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Log;

class UserService
{
    /**
     * @var ACLService
     */
    protected ACLService $aclService;

    /**
     * @var UploadService
     */
    protected UploadService $uploadService;

    /**
     * UserService constructor.
     *
     * @param ACLService $aclService
     * @param UploadService $uploadService
     */
    public function __construct(ACLService $aclService, UploadService $uploadService)
    {
        $this->aclService = $aclService;
        $this->uploadService = $uploadService;
    }

    /**
     * Get all users with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @param string|null $userType
     * @return Collection|LengthAwarePaginator
     */
    public function getAllUsers(?int $perPage = null, array $relations = [], ?string $userType = null): Collection|LengthAwarePaginator
    {
        $query = User::query();

        if ($userType) {
            $query->where('user_type', $userType);
        }

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Create a new user with optional file upload.
     *
     * @param array $data
     * @param array $relations
     * @return User
     */
    public function createUser(array $data, array $relations = []): User
    {
        // Check if email already exists with the same user_type
        $existingUser = User::query()->where('email', $data['email'])
            ->where('user_type', $data['user_type'] ?? null)
            ->first();

        if ($existingUser) {
            throw new InvalidArgumentException('Email already exists for this user type');
        }

        // Handle profile photo upload if provided
        $photoFile = null;
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $photoFile = $data['photo'];
            unset($data['photo']); // Remove from user creation data
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = User::query()->create($data);

        // Upload profile photo if provided
        if ($photoFile) {
            try {
                $upload = $this->uploadService->uploadProfilePhoto($photoFile, $user);
            } catch (Exception $e) {
                Log::error('Profile photo upload failed during user creation: ' . $e->getMessage());
                // Continue with user creation even if photo upload fails
            }
        }

        // Assign default role based on user_type if provided
        if (isset($data['user_type'])) {
            $this->aclService->assignDefaultRolesByUserType($user, $data['user_type']);
        }

        // Assign specific roles if provided
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        // Assign specific permissions if provided
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        if (!empty($relations)) {
            $user->load($relations);
        }

        return $user;
    }

    /**
     * Update an existing user with optional file upload.
     *
     * @param int $id
     * @param array $data
     * @param array $relations
     * @return User|null
     */
    public function updateUser(int $id, array $data, array $relations = []): ?User
    {
        $user = User::query()->find($id);

        if (!$user) {
            return null;
        }

        // Handle profile photo upload if provided
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            try {
                $upload = $this->uploadService->uploadProfilePhoto($data['photo'], $user);
                $data['photo'] = $upload->file_path;
            } catch (Exception $e) {
                Log::error('Profile photo upload failed during user update: ' . $e->getMessage());
                unset($data['photo']); // Remove from update data if upload fails
            }
        }

        // Check if email is being changed and if it already exists for the same user_type
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $existingUser = User::query()->where('email', $data['email'])
                ->where('user_type', $data['user_type'] ?? $user->user_type)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingUser) {
                throw new InvalidArgumentException('Email already exists for this user type');
            }
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        // Update roles if provided
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        // Update permissions if provided
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        if (!empty($relations)) {
            $user->load($relations);
        }

        return $user;
    }

    /**
     * Delete a user and their associated uploads.
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

        // Delete user's profile photo if exists
        if ($user->photo) {
            $this->uploadService->deleteUserProfilePhoto($user);
        }

        // Delete all user uploads
        $uploads = $this->uploadService->getUserUploads($user);
        foreach ($uploads as $upload) {
            $this->uploadService->deleteUpload($upload);
        }

        return $user->delete();
    }

    /**
     * Get user uploads
     *
     * @param int $userId
     * @param int|null $perPage
     * @param string|null $fileType
     * @param string|null $disk
     * @return Collection|LengthAwarePaginator
     * @throws Exception
     */
    public function getUserUploads(int $userId, ?int $perPage = null, ?string $fileType = null, ?string $disk = null): Collection|LengthAwarePaginator
    {
        $user = $this->getUserById($userId);

        if (!$user) {
            throw new Exception('User not found');
        }

        return $this->uploadService->getUserUploads($user, $perPage, $fileType, $disk);
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
     * Search users by name or email.
     *
     * @param string $term
     * @param int|null $perPage
     * @param array $relations
     * @param string|null $userType
     * @return Collection|LengthAwarePaginator
     */
    public function searchUsers(string $term, ?int $perPage = null, array $relations = [], ?string $userType = null): Collection|LengthAwarePaginator
    {
        $query = User::query()
            ->where(function ($q) use ($term) {
                $q->whereLike('name', "%$term%")
                    ->orwhereLike('email', "%$term%");
            });

        if ($userType) {
            $query->where('user_type', $userType);
        }

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Upload profile photo for a user
     *
     * @param int $userId
     * @param UploadedFile $photo
     * @return array
     * @throws Exception
     */
    public function uploadUserProfilePhoto(int $userId, UploadedFile $photo): array
    {
        $user = $this->getUserById($userId);

        if (!$user) {
            throw new Exception('User not found');
        }

        try {
            $upload = $this->uploadService->uploadProfilePhoto($photo, $user);

            return [
                'user' => $user->fresh(),
                'upload' => $upload,
                'photo_url' => $upload->public_url,
                'thumbnails' => [
                    'thumb' => $this->uploadService->getThumbnailUrl($upload, 'thumb'),
                    'small' => $this->uploadService->getThumbnailUrl($upload, 'small'),
                    'medium' => $this->uploadService->getThumbnailUrl($upload, 'medium'),
                ],
            ];
        } catch (Exception $e) {
            throw new Exception('Profile photo upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove profile photo for a user
     *
     * @param int $userId
     * @return User
     * @throws Exception
     */
    public function removeUserProfilePhoto(int $userId): User
    {
        $user = $this->getUserById($userId);

        if (!$user) {
            throw new Exception('User not found');
        }

        if (!$user->photo) {
            throw new Exception('No profile photo to remove');
        }

        $this->uploadService->deleteUserProfilePhoto($user);

        return $user->fresh();
    }

    /**
     * Get user upload statistics
     *
     * @param int $userId
     * @return array
     * @throws Exception
     */
    public function getUserUploadStatistics(int $userId): array
    {
        $user = $this->getUserById($userId);

        if (!$user) {
            throw new Exception('User not found');
        }

        return $this->uploadService->getUserUploadStatistics($user);
    }
}
