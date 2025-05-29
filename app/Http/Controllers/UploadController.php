<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadRequest;
use App\Http\Resources\UploadResource;
use App\Models\Upload;
use App\Services\UploadService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UploadController extends Controller
{
    /**
     * The upload service instance.
     *
     * @var UploadService
     */
    protected UploadService $uploadService;

    /**
     * Create a new controller instance.
     *
     * @param UploadService $uploadService
     */
    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
//        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the user's uploads.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->query('per_page', config('app.per_page'));
        $fileType = $request->query('file_type');
        $disk = $request->query('disk');

        $uploads = $this->uploadService->getUserUploads($user, $perPage, $fileType, $disk);

        return response()->paginated(
            UploadResource::collection($uploads),
            'Uploads retrieved successfully'
        );
    }

    /**
     * Store a newly uploaded file.
     *
     * @param UploadRequest $request
     * @return JsonResponse
     */
    public function store(UploadRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $file = $request->file('file');
            $options = $request->only([
                'disk', 'folder', 'is_public', 'generate_thumbnails',
                'max_file_size', 'allowed_extensions', 'image_quality',
                'image_max_width', 'image_max_height'
            ]);

            // Filter out null values
            $options = array_filter($options, function ($value) {
                return $value !== null;
            });

            $upload = $this->uploadService->uploadFile($file, $user, $options);

            return response()->success(
                new UploadResource($upload),
                'File uploaded successfully',
                201
            );
        } catch (Exception $e) {
            return response()->error(
                'File upload failed: ' . $e->getMessage(),
                null,
                422
            );
        }
    }

    /**
     * Store multiple uploaded files.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeMultiple(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|max:10240', // 10MB per file
        ]);

        try {
            $user = Auth::user();
            $files = $request->file('files');
            $options = $request->only([
                'disk', 'folder', 'is_public', 'generate_thumbnails',
                'max_file_size', 'allowed_extensions', 'image_quality',
                'image_max_width', 'image_max_height'
            ]);

            // Filter out null values
            $options = array_filter($options, function ($value) {
                return $value !== null;
            });

            $uploads = $this->uploadService->uploadMultipleFiles($files, $user, $options);

            return response()->success(
                UploadResource::collection($uploads),
                'Files uploaded successfully',
                201
            );
        } catch (Exception $e) {
            return response()->error(
                'File upload failed: ' . $e->getMessage(),
                null,
                422
            );
        }
    }

    /**
     * Display the specified upload.
     *
     * @param Upload $upload
     * @return JsonResponse
     */
    public function show(Upload $upload): JsonResponse
    {
        $user = Auth::user();

        // Check if user owns the file or if it's public
        if ($upload->user_id !== $user->id && !$upload->is_public) {
            return response()->error('Unauthorized', null, 403);
        }

        return response()->success(
            new UploadResource($upload),
            'Upload retrieved successfully'
        );
    }

    /**
     * Update the specified upload metadata.
     *
     * @param Request $request
     * @param Upload $upload
     * @return JsonResponse
     */
    public function update(Request $request, Upload $upload): JsonResponse
    {
        $user = Auth::user();

        // Check if user owns the file
        if ($upload->user_id !== $user->id) {
            return response()->error('Unauthorized', null, 403);
        }

        $request->validate([
            'original_name' => 'sometimes|string|max:255',
            'is_public' => 'sometimes|boolean',
            'metadata' => 'sometimes|array',
        ]);

        $upload = $this->uploadService->updateUpload($upload, $request->only(['original_name', 'is_public', 'metadata']));

        return response()->success(
            new UploadResource($upload),
            'Upload updated successfully'
        );
    }

    /**
     * Remove the specified upload.
     *
     * @param Upload $upload
     * @return JsonResponse
     */
    public function destroy(Upload $upload): JsonResponse
    {
        $user = Auth::user();

        // Check if user owns the file
        if ($upload->user_id !== $user->id) {
            return response()->error('Unauthorized', null, 403);
        }

        if ($this->uploadService->deleteUpload($upload)) {
            return response()->success(null, 'Upload deleted successfully');
        }

        return response()->error('Failed to delete upload', null, 500);
    }

    /**
     * Download the specified upload.
     *
     * @param Upload $upload
     * @return StreamedResponse
     */
    public function download(Upload $upload): StreamedResponse
    {
        $user = Auth::user();

        // Check if user owns the file or if it's public
        if ($upload->user_id !== $user->id && !$upload->is_public) {
            abort(403, 'Unauthorized');
        }

        if (!$upload->fileExists()) {
            abort(404, 'File not found');
        }

        $stream = $this->uploadService->getFileStream($upload);

        if (!$stream) {
            abort(404, 'File not found');
        }

        return response()->stream(
            function () use ($stream) {
                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => $upload->mime_type,
                'Content-Disposition' => 'attachment; filename="' . $upload->original_name . '"',
                'Content-Length' => $upload->file_size,
            ]
        );
    }

    /**
     * Get thumbnail for an image upload.
     *
     * @param Upload $upload
     * @param string $size
     * @return JsonResponse
     */
    public function thumbnail(Upload $upload, string $size = 'thumb'): JsonResponse
    {
        $user = Auth::user();

        // Check if user owns the file or if it's public
        if ($upload->user_id !== $user->id && !$upload->is_public) {
            return response()->error('Unauthorized', null, 403);
        }

        if (!$upload->isImage()) {
            return response()->error('File is not an image', null, 400);
        }

        $thumbnailUrl = $this->uploadService->getThumbnailUrl($upload, $size);

        if (!$thumbnailUrl) {
            return response()->error('Thumbnail not found', null, 404);
        }

        return response()->success([
            'thumbnail_url' => $thumbnailUrl,
            'size' => $size,
        ], 'Thumbnail retrieved successfully');
    }

    /**
     * Get temporary URL for private files.
     *
     * @param Upload $upload
     * @param Request $request
     * @return JsonResponse
     */
    public function temporaryUrl(Upload $upload, Request $request): JsonResponse
    {
        $user = Auth::user();

        // Check if user owns the file
        if ($upload->user_id !== $user->id) {
            return response()->error('Unauthorized', null, 403);
        }

        $request->validate([
            'expires_in' => 'sometimes|integer|min:1|max:1440', // Max 24 hours
        ]);

        $expiresIn = $request->input('expires_in', 60); // Default 1 hour
        $expiration = now()->addMinutes($expiresIn);

        $temporaryUrl = $this->uploadService->getTemporaryUrl($upload, $expiration);

        if (!$temporaryUrl) {
            return response()->error('Temporary URL not supported for this storage driver', null, 400);
        }

        return response()->success([
            'temporary_url' => $temporaryUrl,
            'expires_at' => $expiration->toISOString(),
        ], 'Temporary URL generated successfully');
    }

    /**
     * Get upload statistics for the authenticated user.
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->uploadService->getUserUploadStatistics($user);

        return response()->success($stats, 'Upload statistics retrieved successfully');
    }
}
