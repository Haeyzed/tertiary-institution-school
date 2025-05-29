<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\ProfilePhotoRequest;
use App\Http\Resources\UploadResource;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use InvalidArgumentException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

/**
 * @tags Auth
 */
class AuthController extends Controller implements HasMiddleware
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
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', except: ['login', 'register', 'forgotPassword', 'resetPassword', 'verifyEmail']),
        ];
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
            $relations = $request->input('with', []);
            $result = $this->authService->login($request->validated(), $relations);

            return response()->success([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
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
        try {
            $relations = $request->input('with', []);
            $result = $this->authService->register($request->validated(), $relations);

            return response()->success([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ], 'Registration successful', 201);
        } catch (InvalidArgumentException $e) {
            return response()->error($e->getMessage(), null, 422);
        }
    }

    /**
     * Logout the authenticated user.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
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
        $relations = $request->input('with', []);
        $result = $this->authService->refreshToken($relations);

        return response()->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ], 'Token refreshed successfully');
    }

    /**
     * Get the authenticated user's profile.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws JWTException
     */
    public function profile(Request $request): JsonResponse
    {
        $relations = $request->input('with', []);
        $user = $this->authService->getProfile($relations);

        $resource = new UserResource($user);
        if ($request->has('include_all_permissions')) {
            $resource->additional(['include_all_permissions' => true]);
        }

        return response()->success(
            $resource,
            'Profile retrieved successfully'
        );
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     * @throws JWTException
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $relations = $request->input('with', []);
            $user = $this->authService->updateProfile($request->validated(), $relations);

            return response()->success(
                new UserResource($user),
                'Profile updated successfully'
            );
        } catch (InvalidArgumentException $e) {
            return response()->error($e->getMessage(), null, 422);
        }
    }

    /**
     * Upload user profile photo.
     *
     * @param ProfilePhotoRequest $request
     * @return JsonResponse
     */
    public function uploadProfilePhoto(ProfilePhotoRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->uploadProfilePhoto($request->file('photo'));

            return response()->success([
                'user' => new UserResource($result['user']),
                'upload' => new UploadResource($result['upload']),
                'photo_url' => $result['photo_url'],
                'thumbnails' => $result['thumbnails'],
            ], 'Profile photo uploaded successfully');

        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                null,
                422
            );
        }
    }

    /**
     * Remove user profile photo.
     *
     * @return JsonResponse
     */
    public function removeProfilePhoto(): JsonResponse
    {
        try {
            $user = $this->authService->removeProfilePhoto();

            return response()->success(
                new UserResource($user),
                'Profile photo removed successfully'
            );

        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                null,
                400
            );
        }
    }

    /**
     * Change the authenticated user's password.
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     * @throws JWTException
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
        $success = $this->authService->forgotPassword(
            $request->email,
            $request->user_type
        );

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

    /**
     * Verify email address.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $userId = $request->route('id');
        $hash = $request->route('hash');
        $userType = $request->query('user_type');

        $success = $this->authService->verifyEmail($userId, $hash, $userType);

        if ($success) {
            return response()->success(null, 'Email verified successfully');
        }

        return response()->error('Invalid verification link', null, 400);
    }

    /**
     * Resend email verification notification.
     *
     * @return JsonResponse
     * @throws JWTException
     */
    public function resendEmailVerification(): JsonResponse
    {
        $success = $this->authService->resendEmailVerification();

        if ($success) {
            return response()->success(null, 'Verification email sent');
        }

        return response()->error('Email already verified', null, 400);
    }
}
