<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfilePhotoRequest;
use App\Http\Resources\UploadResource;
use App\Http\Resources\UserResource;
use App\Models\Upload;
use App\Services\FileStorageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Example controller demonstrating profile photo upload use case.
 */
class UserProfileController extends Controller
{
    /**
     * The file storage service instance.
     *
     * @var FileStorageService
     */
    protected FileStorageService $fileStorageService;

    /**
     * Create a new controller instance.
     *
     * @param FileStorageService $fileStorageService
     */
    public function __construct(FileStorageService $fileStorageService)
    {
        $this->fileStorageService = $fileStorageService;
//        $this->middleware('auth:api');
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
            $user = Auth::user();
            $file = $request->file('photo');

            // Configure upload options for profile photos
            $options = [
                'disk' => 'public',
                'folder' => 'uploads/profiles',
                'is_public' => true,
                'generate_thumbnails' => true,
                'max_file_size' => 5242880, // 5MB
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'image_quality' => 90,
                'image_max_width' => 1024,
                'image_max_height' => 1024,
            ];

            // Upload the file
            $upload = $this->fileStorageService->upload($file, $options);

            // Delete old profile photo if exists
            if ($user->photo) {
                $oldUpload = Upload::query()->where('user_id', $user->id)
                    ->where('file_path', $user->photo)
                    ->first();

                if ($oldUpload) {
                    $this->fileStorageService->delete($oldUpload);
                }
            }

            // Update user's photo path
            $user->update(['photo' => $upload->file_path]);

            return response()->success([
                'user' => new UserResource($user),
                'upload' => new UploadResource($upload),
                'photo_url' => $upload->public_url,
                'thumbnails' => [
                    'thumb' => $this->fileStorageService->getThumbnailUrl($upload, 'thumb'),
                    'small' => $this->fileStorageService->getThumbnailUrl($upload, 'small'),
                    'medium' => $this->fileStorageService->getThumbnailUrl($upload, 'medium'),
                ],
            ], 'Profile photo uploaded successfully');

        } catch (Exception $e) {
            return response()->error(
                'Profile photo upload failed: ' . $e->getMessage(),
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
            $user = Auth::user();

            if (!$user->photo) {
                return response()->error('No profile photo to remove', null, 400);
            }

            // Find and delete the upload record
            $upload = Upload::query()->where('user_id', $user->id)
                ->where('file_path', $user->photo)
                ->first();

            if ($upload) {
                $this->fileStorageService->delete($upload);
            }

            // Remove photo path from user
            $user->update(['photo' => null]);

            return response()->success(
                new UserResource($user),
                'Profile photo removed successfully'
            );

        } catch (Exception $e) {
            return response()->error(
                'Profile photo removal failed: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Get user profile with photo URLs.
     *
     * @return JsonResponse
     */
    public function getProfile(): JsonResponse
    {
        $user = Auth::user();
        $profileData = new UserResource($user);

        // Add photo URLs if user has a profile photo
        if ($user->photo) {
            $upload = Upload::query()->where('user_id', $user->id)
                ->where('file_path', $user->photo)
                ->first();

            if ($upload) {
                $profileData->additional([
                    'photo_urls' => [
                        'original' => $upload->public_url,
                        'thumbnails' => [
                            'thumb' => $this->fileStorageService->getThumbnailUrl($upload, 'thumb'),
                            'small' => $this->fileStorageService->getThumbnailUrl($upload, 'small'),
                            'medium' => $this->fileStorageService->getThumbnailUrl($upload, 'medium'),
                        ],
                    ],
                ]);
            }
        }

        return response()->success(
            $profileData,
            'Profile retrieved successfully'
        );
    }
}
