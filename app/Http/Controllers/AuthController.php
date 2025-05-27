<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Auth
 */
class AuthController extends Controller
{
    /**
     * The auth service instance.
     *
     * @var AuthService
     */
    protected AuthService $authService;

    /**
     * Create a new controller instance.
     *
     * @param AuthService $authService
     * @return void
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Authenticate a user and return a token.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            return response()->success([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ], 'Login successful');
        } catch (AuthenticationException $e) {
            return response()->error($e->getMessage(), null, 401);
        }
    }

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Registration successful', 201);
    }

    /**
     * Logout the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout();

        return response()->success(null, 'Logout successful');
    }

    /**
     * Refresh the authentication token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $result = $this->authService->refreshToken();

        return response()->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Token refreshed successfully');
    }

    /**
     * Get the authenticated user's profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $this->authService->getProfile();

        return response()->success(
            new UserResource($user),
            'Profile retrieved successfully'
        );
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile($request->validated());

        return response()->success(
            new UserResource($user),
            'Profile updated successfully'
        );
    }

    /**
     * Change the authenticated user's password.
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->changePassword(
                $request->current_password,
                $request->new_password
            );

            return response()->success(null, 'Password changed successfully');
        } catch (AuthenticationException $e) {
            return response()->error($e->getMessage(), null, 401);
        }
    }

    /**
     * Send a password reset link to the user.
     *
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $success = $this->authService->forgotPassword($request->email);

        if ($success) {
            return response()->success(null, 'Password reset link sent to your email');
        }

        return response()->error('Failed to send password reset link', null, 400);
    }

    /**
     * Reset the user's password.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $success = $this->authService->resetPassword($request->validated());

        if ($success) {
            return response()->success(null, 'Password has been reset successfully');
        }

        return response()->error('Failed to reset password', null, 400);
    }
}
