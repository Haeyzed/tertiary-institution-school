<?php

namespace App\Services;

use App\Enums\FileTypeEnum;
use App\Models\Upload;
use App\Models\User;
use DateTimeInterface;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Interfaces\ImageInterface;
use Log;

class UploadService
{
    /**
     * Default configuration
     */
    protected array $config = [
        'disk' => 's3',
        'folder' => 'uploads',
        'is_public' => true,
        'generate_thumbnails' => true,
        'max_file_size' => 10485760, // 10MB
        'allowed_extensions' => [],
        'image_quality' => 90,
        'image_max_width' => 2048,
        'image_max_height' => 2048,
    ];

    /**
     * Image manager instance
     */
    protected ImageManager $imageManager;

    /**
     * UploadService constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Upload a file for a specific user
     *
     * @param UploadedFile $file
     * @param User $user
     * @param array $options
     * @return Upload
     * @throws Exception
     */
    public function uploadFile(UploadedFile $file, User $user, array $options = []): Upload
    {
        $config = array_merge($this->config, $options);

        // Validate file
        $this->validateFile($file, $config);

        // Generate file information
        $fileInfo = $this->generateFileInfo($file, $config);

        // Store the file
        $filePath = $this->storeFile($file, $fileInfo, $config);

        // Create upload record
        $upload = $this->createUploadRecord($file, $filePath, $fileInfo, $config, $user);

        // Process image if needed (only for local storage or if thumbnails are explicitly enabled)
        if ($upload->isImage() && $config['generate_thumbnails'] && $this->shouldGenerateThumbnails($config['disk'])) {
            $this->processImage($upload, $config);
        }

        return $upload;
    }

    /**
     * Upload multiple files for a specific user
     *
     * @param array $files
     * @param User $user
     * @param array $options
     * @return array
     */
    public function uploadMultipleFiles(array $files, User $user, array $options = []): array
    {
        $uploads = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                try {
                    $uploads[] = $this->uploadFile($file, $user, $options);
                } catch (Exception $e) {
                    Log::error('File upload failed: ' . $e->getMessage());
                }
            }
        }

        return $uploads;
    }

    /**
     * Upload profile photo for user
     *
     * @param UploadedFile $file
     * @param User $user
     * @return Upload
     * @throws Exception
     */
    public function uploadProfilePhoto(UploadedFile $file, User $user): Upload
    {
        $options = [
            'disk' => config('filesystems.default'),
            'folder' => 'uploads/profiles',
            'is_public' => true,
            'generate_thumbnails' => false, // Disable thumbnails for profile photos to avoid S3 issues
            'max_file_size' => 5242880, // 5MB
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'image_quality' => 90,
            'image_max_width' => 1024,
            'image_max_height' => 1024,
        ];

        // Delete old profile photo if exists
        if ($user->photo) {
            $this->deleteUserProfilePhoto($user);
        }

        $upload = $this->uploadFile($file, $user, $options);

        // Update user's photo path
        $user->update(['photo' => $upload->file_path]);

        return $upload;
    }

    /**
     * Delete user's profile photo
     *
     * @param User $user
     * @return bool
     */
    public function deleteUserProfilePhoto(User $user): bool
    {
        if (!$user->photo) {
            return false;
        }

        $upload = Upload::query()->where('user_id', $user->id)
            ->where('file_path', $user->photo)
            ->first();

        if ($upload) {
            $this->deleteUpload($upload);
        }

        $user->update(['photo' => null]);
        return true;
    }

    /**
     * Get user uploads with pagination
     *
     * @param string $term
     * @param User $user
     * @param int|null $perPage
     * @param string|null $fileType
     * @param string|null $disk
     * @return Collection|LengthAwarePaginator
     */
    public function getUserUploads(string $term, User $user, ?int $perPage = null, ?string $fileType = null, ?string $disk = null): Collection|LengthAwarePaginator
    {
        $query = Upload::query()->where(function ($q) use ($term) {
                $q->whereLike('original_name', "%$term%");
            })->byUser($user->id)
            ->with('user')
            ->latest('uploaded_at');

        if ($fileType) {
            $query->byFileType(FileTypeEnum::from($fileType));
        }

        if ($disk) {
            $query->byDisk($disk);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get upload by ID for user
     *
     * @param int $uploadId
     * @param User $user
     * @return Upload|null
     */
    public function getUserUpload(int $uploadId, User $user): ?Upload
    {
        return Upload::query()->where('id', $uploadId)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Update upload metadata
     *
     * @param Upload $upload
     * @param array $data
     * @return Upload
     */
    public function updateUpload(Upload $upload, array $data): Upload
    {
        $upload->update($data);
        return $upload;
    }

    /**
     * Delete an upload
     *
     * @param Upload $upload
     * @return bool
     */
    public function deleteUpload(Upload $upload): bool
    {
        try {
            // Delete file from storage
            $upload->deleteFile();

            // Delete thumbnails if they exist
            $this->deleteThumbnails($upload);

            // Delete upload record
            $upload->delete();

            return true;
        } catch (Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get file content
     *
     * @param Upload $upload
     * @return string|null
     */
    public function getFileContent(Upload $upload): ?string
    {
        return $upload->getFileContent();
    }

    /**
     * Get file stream
     *
     * @param Upload $upload
     * @return resource|null
     */
    public function getFileStream(Upload $upload)
    {
        if ($upload->fileExists()) {
            return Storage::disk($upload->disk)->readStream($upload->file_path);
        }

        return null;
    }

    /**
     * Generate a temporary URL for private files
     *
     * @param Upload $upload
     * @param DateTimeInterface $expiration
     * @return string|null
     */
    public function getTemporaryUrl(Upload $upload, DateTimeInterface $expiration): ?string
    {
        return $upload->getTemporaryUrl($expiration);
    }

    /**
     * Get thumbnail URL
     *
     * @param Upload $upload
     * @param string $size
     * @return string|null
     */
    public function getThumbnailUrl(Upload $upload, string $size = 'thumb'): ?string
    {
        return $upload->getThumbnailUrl($size);
    }

    /**
     * Get upload statistics for user
     *
     * @param User $user
     * @return array
     */
    public function getUserUploadStatistics(User $user): array
    {
        $stats = [
            'total_uploads' => Upload::query()->byUser($user->id)->count(),
            'total_size' => Upload::query()->byUser($user->id)->sum('file_size'),
            'by_type' => Upload::query()->byUser($user->id)
                ->selectRaw('file_type, COUNT(*) as count, SUM(file_size) as total_size')
                ->groupBy('file_type')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->file_type->value => [
                        'count' => $item->count,
                        'total_size' => $item->total_size,
                        'human_size' => $this->formatBytes($item->total_size),
                    ]];
                }),
            'by_disk' => Upload::query()->byUser($user->id)
                ->selectRaw('disk, COUNT(*) as count, SUM(file_size) as total_size')
                ->groupBy('disk')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->disk => [
                        'count' => $item->count,
                        'total_size' => $item->total_size,
                        'human_size' => $this->formatBytes($item->total_size),
                    ]];
                }),
            'recent_uploads' => Upload::query()->byUser($user->id)
                ->latest('uploaded_at')
                ->limit(5)
                ->get()
                ->map(function ($upload) {
                    return [
                        'id' => $upload->id,
                        'original_name' => $upload->original_name,
                        'file_type' => $upload->file_type->value,
                        'file_size' => $upload->human_file_size,
                        'uploaded_at' => $upload->uploaded_at->toISOString(),
                    ];
                }),
        ];

        $stats['human_total_size'] = $this->formatBytes($stats['total_size']);

        return $stats;
    }

    /**
     * Check if thumbnails should be generated for the given disk
     *
     * @param string $disk
     * @return bool
     */
    protected function shouldGenerateThumbnails(string $disk): bool
    {
        // For cloud storage like S3, we might want to skip thumbnail generation
        // to avoid downloading and re-uploading files
        $cloudDisks = ['s3', 'spaces', 'gcs', 'azure'];

        return !in_array($disk, $cloudDisks);
    }

    /**
     * Validate uploaded file
     *
     * @param UploadedFile $file
     * @param array $config
     * @throws Exception
     */
    protected function validateFile(UploadedFile $file, array $config): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new Exception('Invalid file upload');
        }

        // Check file size
        if ($file->getSize() > $config['max_file_size']) {
            throw new Exception('File size exceeds maximum allowed size');
        }

        // Check file extension
        if (!empty($config['allowed_extensions'])) {
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $config['allowed_extensions'])) {
                throw new Exception('File type not allowed');
            }
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!$this->isAllowedMimeType($mimeType)) {
            throw new Exception('File MIME type not allowed');
        }
    }

    /**
     * Generate file information
     *
     * @param UploadedFile $file
     * @param array $config
     * @return array
     */
    protected function generateFileInfo(UploadedFile $file, array $config): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $fileType = FileTypeEnum::fromMimeType($mimeType);

        // Generate unique filename
        $fileName = $this->generateUniqueFileName($originalName, $extension);

        // Generate folder path
        $folder = $this->generateFolderPath($config['folder'], $fileType);

        return [
            'original_name' => $originalName,
            'file_name' => $fileName,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'file_type' => $fileType,
            'folder' => $folder,
        ];
    }

    /**
     * Store file to disk
     *
     * @param UploadedFile $file
     * @param array $fileInfo
     * @param array $config
     * @return string
     */
    protected function storeFile(UploadedFile $file, array $fileInfo, array $config): string
    {
        $disk = Storage::disk($config['disk']);

        // Store file
        $filePath = $file->storeAs(
            $fileInfo['folder'],
            $fileInfo['file_name'],
            $config['disk']
        );

        // Set file visibility for cloud storage
        if ($config['is_public'] && in_array($config['disk'], ['s3', 'spaces', 'gcs'])) {
            try {
                $disk->setVisibility($filePath, 'public');
            } catch (Exception $e) {
                Log::warning('Failed to set file visibility: ' . $e->getMessage());
            }
        }

        return $filePath;
    }

    /**
     * Create upload record in database
     *
     * @param UploadedFile $file
     * @param string $filePath
     * @param array $fileInfo
     * @param array $config
     * @param User $user
     * @return Upload
     */
    protected function createUploadRecord(UploadedFile $file, string $filePath, array $fileInfo, array $config, User $user): Upload
    {
        $metadata = [
            'original_extension' => $fileInfo['extension'],
            'upload_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        // Add image metadata if it's an image
        if ($fileInfo['file_type'] === FileTypeEnum::IMAGE) {
            $imageSize = getimagesize($file->getPathname());
            if ($imageSize) {
                $metadata['width'] = $imageSize[0];
                $metadata['height'] = $imageSize[1];
            }
        }

        return Upload::create([
            'user_id' => $user->id,
            'original_name' => $fileInfo['original_name'],
            'file_name' => $fileInfo['file_name'],
            'file_path' => $filePath,
            'file_type' => $fileInfo['file_type'],
            'mime_type' => $fileInfo['mime_type'],
            'file_size' => $file->getSize(),
            'disk' => $config['disk'],
            'folder' => $fileInfo['folder'],
            'is_public' => $config['is_public'],
            'metadata' => $metadata,
            'uploaded_at' => now(),
        ]);
    }

    /**
     * Process image (resize, optimize, generate thumbnails)
     *
     * @param Upload $upload
     * @param array $config
     */
    protected function processImage(Upload $upload, array $config): void
    {
        try {
            // Skip image processing for cloud storage to avoid complexity
            if (in_array($upload->disk, ['s3', 'spaces', 'gcs'])) {
                Log::info('Skipping image processing for cloud storage: ' . $upload->disk);
                return;
            }

            $filePath = Storage::disk($upload->disk)->path($upload->file_path);

            // Read image using new Intervention Image 3.x API
            $image = $this->imageManager->read($filePath);

            // Get original dimensions
            $originalWidth = $image->width();
            $originalHeight = $image->height();

            // Resize if needed
            if ($originalWidth > $config['image_max_width'] || $originalHeight > $config['image_max_height']) {
                $image->scaleDown(
                    width: $config['image_max_width'],
                    height: $config['image_max_height']
                );
            }

            // Save optimized image
            $encoded = $image->toJpeg($config['image_quality']);
            file_put_contents($filePath, $encoded);

            // Generate thumbnails
            $this->generateThumbnails($upload, $image);

        } catch (Exception $e) {
            Log::error('Image processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate thumbnails for images
     *
     * @param Upload $upload
     * @param ImageInterface $image
     */
    protected function generateThumbnails(Upload $upload, ImageInterface $image): void
    {
        $thumbnailSizes = [
            'thumb' => [150, 150],
            'small' => [300, 300],
            'medium' => [600, 600],
        ];

        foreach ($thumbnailSizes as $size => [$width, $height]) {
            try {
                // Clone the image for thumbnail generation
                $thumbnailImage = clone $image;

                // Resize to fit within dimensions while maintaining aspect ratio
                $thumbnailImage->cover($width, $height);

                $thumbnailPath = $this->getThumbnailPath($upload->file_path, $size);
                $fullThumbnailPath = Storage::disk($upload->disk)->path($thumbnailPath);

                // Create directory if it doesn't exist
                $directory = dirname($fullThumbnailPath);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                // Save thumbnail
                $encoded = $thumbnailImage->toJpeg(80);
                file_put_contents($fullThumbnailPath, $encoded);

                // Update metadata with thumbnail info
                $metadata = $upload->metadata ?? [];
                $metadata['thumbnails'][$size] = $thumbnailPath;
                $upload->update(['metadata' => $metadata]);

            } catch (Exception $e) {
                Log::error("Thumbnail generation failed for size {$size}: " . $e->getMessage());
            }
        }
    }

    /**
     * Delete thumbnails
     *
     * @param Upload $upload
     */
    protected function deleteThumbnails(Upload $upload): void
    {
        if (isset($upload->metadata['thumbnails'])) {
            foreach ($upload->metadata['thumbnails'] as $thumbnailPath) {
                try {
                    Storage::disk($upload->disk)->delete($thumbnailPath);
                } catch (Exception $e) {
                    Log::error('Thumbnail deletion failed: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Generate unique filename
     *
     * @param string $originalName
     * @param string $extension
     * @return string
     */
    protected function generateUniqueFileName(string $originalName, string $extension): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $name = Str::slug($name);
        $uuid = Str::uuid();

        return $name . '_' . $uuid . '.' . $extension;
    }

    /**
     * Generate folder path
     *
     * @param string $baseFolder
     * @param FileTypeEnum $fileType
     * @return string
     */
    protected function generateFolderPath(string $baseFolder, FileTypeEnum $fileType): string
    {
        $year = date('Y');
        $month = date('m');

        return $baseFolder . '/' . $fileType->value . '/' . $year . '/' . $month;
    }

    /**
     * Get thumbnail path
     *
     * @param string $originalPath
     * @param string $size
     * @return string
     */
    protected function getThumbnailPath(string $originalPath, string $size): string
    {
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];

        return $directory . '/thumbnails/' . $filename . '_' . $size . '.' . $extension;
    }

    /**
     * Check if MIME type is allowed
     *
     * @param string $mimeType
     * @return bool
     */
    protected function isAllowedMimeType(string $mimeType): bool
    {
        $allowedMimeTypes = [
            // Images
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/bmp', 'image/tiff',
            // Documents
            'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain', 'text/csv', 'application/rtf',
            // Videos
            'video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/webm', 'video/ogg',
            // Audio
            'audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4', 'audio/webm', 'audio/flac',
            // Archives
            'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar', 'application/gzip',
        ];

        return in_array($mimeType, $allowedMimeTypes);
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
