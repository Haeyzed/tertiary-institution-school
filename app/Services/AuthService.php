<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Log;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Class AuthService
 *
 * Handles authentication-related operations using JWT authentication.
 *
 * @package App\Services
 */
class AuthService
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
     * AuthService constructor.
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
     * Attempt to authenticate a user with relations.
     *
     * @param array $credentials
     * @param array $relations
     * @return array
     * @throws AuthenticationException
     */
    public function login(array $credentials, array $relations = []): array
    {
        // Add user_type to the query if provided
        $query = ['email' => $credentials['email']];
        if (isset($credentials['user_type'])) {
            $query['user_type'] = $credentials['user_type'];
        }

        // Find the user
        $user = User::query()->where($query)->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        // Generate token
        $token = JWTAuth::fromUser($user);

        if (!empty($relations)) {
            $user->load($relations);
        }

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];
    }

    /**
     * Register a new user with relations and optional file upload.
     *
     * @param array $data
     * @param array $relations
     * @return array
     */
    public function register(array $data, array $relations = []): array
    {
        // Check if email already exists with the same user_type
        $existingUser = User::query()->where('email', $data['email'])
            ->where('user_type', $data['user_type'] ?? null)
            ->first();

        if ($existingUser) {
            throw new InvalidArgumentException('Email already exists for this user type');
        }

        // Handle profile photo upload if provided
        $photoPath = null;
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            // We'll upload the photo after creating the user
            $photoFile = $data['photo'];
            unset($data['photo']); // Remove from user creation data
        }

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'photo' => null, // Will be set after upload
            'user_type' => $data['user_type'] ?? null,
        ]);

        // Upload profile photo if provided
        if (isset($photoFile)) {
            try {
                $upload = $this->uploadService->uploadProfilePhoto($photoFile, $user);
                $photoPath = $upload->file_path;
            } catch (Exception $e) {
                Log::error('Profile photo upload failed during registration: ' . $e->getMessage());
                // Continue with registration even if photo upload fails
            }
        }

        // Assign default role based on user_type
        if ($user->user_type) {
            $this->aclService->assignDefaultRolesByUserType($user, $user->user_type->value);
        }

        // Fire the registered event
        event(new Registered($user));

        $token = JWTAuth::fromUser($user);

        if (!empty($relations)) {
            $user->load($relations);
        }

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];
    }

    /**
     * Upload profile photo for authenticated user
     *
     * @param UploadedFile $photo
     * @return array
     * @throws JWTException
     */
    public function uploadProfilePhoto(UploadedFile $photo): array
    {
        $user = JWTAuth::parseToken()->authenticate();

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
     * Logout the authenticated user.
     *
     * @return bool
     */
    public function logout(): bool
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return true;
    }

    /**
     * Refresh the authentication token.
     *
     * @param array $relations
     * @return array
     */
    public function refreshToken(array $relations = []): array
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());
        $user = JWTAuth::setToken($token)->toUser();

        if (!empty($relations)) {
            $user->load($relations);
        }

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];
    }

    /**
     * Get the authenticated user's profile with relations.
     *
     * @param array $relations
     * @return User
     * @throws JWTException
     */
    public function getProfile(array $relations = []): User
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!empty($relations)) {
            $user->load($relations);
        }

        return $user;
    }

    /**
     * Update the authenticated user's profile with optional file upload.
     *
     * @param array $data
     * @param array $relations
     * @return User
     * @throws JWTException
     */
    public function updateProfile(array $data, array $relations = []): User
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Handle profile photo upload if provided
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            try {
                $upload = $this->uploadService->uploadProfilePhoto($data['photo'], $user);
                $data['photo'] = $upload->file_path;
            } catch (Exception $e) {
                Log::error('Profile photo upload failed during update: ' . $e->getMessage());
                unset($data['photo']); // Remove from update data if upload fails
            }
        }

        // Remove password from update data if empty
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        } elseif (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Check if email is being changed and if it already exists for the same user_type
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $existingUser = User::query()->where('email', $data['email'])
                ->where('user_type', $user->user_type)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingUser) {
                throw new InvalidArgumentException('Email already exists for this user type');
            }
        }

        $user->update($data);

        if (!empty($relations)) {
            $user->load($relations);
        }

        return $user;
    }

    /**
     * Remove profile photo for authenticated user
     *
     * @return User
     * @throws JWTException
     */
    public function removeProfilePhoto(): User
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user->photo) {
            throw new Exception('No profile photo to remove');
        }

        $this->uploadService->deleteUserProfilePhoto($user);

        return $user->fresh();
    }

    /**
     * Change the authenticated user's password.
     *
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws AuthenticationException|JWTException
     */
    public function changePassword(string $currentPassword, string $newPassword): bool
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!Hash::check($currentPassword, $user->password)) {
            throw new AuthenticationException('Current password is incorrect');
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return true;
    }

    /**
     * Send a password reset link to the user.
     *
     * @param string $email
     * @param string|null $userType
     * @return bool
     */
    public function forgotPassword(string $email, ?string $userType = null): bool
    {
        $query = ['email' => $email];

        if ($userType) {
            $query['user_type'] = $userType;
        }

        $user = User::query()->where($query)->first();

        if (!$user) {
            return false;
        }

        $status = Password::sendResetLink(['email' => $email, 'user_type' => $userType]);

        return $status === Password::RESET_LINK_SENT;
    }

    /**
     * Reset the user's password.
     *
     * @param array $data
     * @return bool
     */
    public function resetPassword(array $data): bool
    {
        $status = Password::reset(
            $data,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET;
    }

    /**
     * Verify email address.
     *
     * @param int $userId
     * @param string $hash
     * @param string|null $userType
     * @return bool
     */
    public function verifyEmail(int $userId, string $hash, ?string $userType = null): bool
    {
        $query = User::query();

        if ($userType) {
            $query->where('user_type', $userType);
        }

        $user = $query->findOrFail($userId);

        if (!hash_equals((string)$hash, sha1($user->getEmailForVerification()))) {
            return false;
        }

        if ($user->hasVerifiedEmail()) {
            return true;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return true;
        }

        return false;
    }

    /**
     * Resend email verification notification.
     *
     * @return bool
     * @throws JWTException
     */
    public function resendEmailVerification(): bool
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->sendEmailVerificationNotification();
        return true;
    }
}
