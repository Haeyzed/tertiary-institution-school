<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Class AuthService
 * 
 * Handles authentication-related operations such as login, registration, password reset, etc.
 * 
 * @package App\Services
 */
class AuthService
{
    /**
     * Attempt to authenticate a user.
     *
     * @param array $credentials
     * @return array
     * @throws AuthenticationException
     */
    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            throw new AuthenticationException('Invalid credentials');
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout the authenticated user.
     *
     * @return bool
     */
    public function logout(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Revoke all tokens
        $user->tokens()->delete();
        
        return true;
    }

    /**
     * Refresh the authentication token.
     *
     * @return array
     */
    public function refreshToken(): array
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Revoke all tokens
        $user->tokens()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Get the authenticated user's profile.
     *
     * @return User
     */
    public function getProfile(): User
    {
        /** @var User $user */
        $user = Auth::user();
        
        return $user;
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param array $data
     * @return User
     */
    public function updateProfile(array $data): User
    {
        /** @var User $user */
        $user = Auth::user();
        
        $user->update($data);
        
        return $user;
    }

    /**
     * Change the authenticated user's password.
     *
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws AuthenticationException
     */
    public function changePassword(string $currentPassword, string $newPassword): bool
    {
        /** @var User $user */
        $user = Auth::user();
        
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
     * @return bool
     */
    public function forgotPassword(string $email): bool
    {
        $status = Password::sendResetLink(['email' => $email]);
        
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
}
