<?php

namespace App\Services;

use App\Enums\FileTypeEnum;
use App\Models\Upload;
use DateTimeInterface;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Log;

class FileStorageService
{
    /**
     * Default configuration
     */
    protected array $config = [
        'disk' => 'public',
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
     * FileStorageService constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Upload multiple files
     *
     * @param array $files
     * @param array $options
     * @return array
     */
    public function uploadMultiple(array $files, array $options = []): array
    {
        $uploads = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                try {
                    $uploads[] = $this->upload($file, $options);
                } catch (Exception $e) {
                    // Log error and continue with other files
                    Log::error('File upload failed: ' . $e->getMessage());
                }
            }
        }

        return $uploads;
    }

    /**
     * Upload a file
     *
     * @param UploadedFile $file
     * @param array $options
     * @return Upload
     * @throws Exception
     */
    public function upload(UploadedFile $file, array $options = []): Upload
    {
        $config = array_merge($this->config, $options);

        // Validate file
        $this->validateFile($file, $config);

        // Generate file information
        $fileInfo = $this->generateFileInfo($file, $config);

        // Store the file
        $filePath = $this->storeFile($file, $fileInfo, $config);

        // Create upload record
        $upload = $this->createUploadRecord($file, $filePath, $fileInfo, $config);

        // Process image if needed
        if ($upload->isImage() && $config['generate_thumbnails']) {
            $this->processImage($upload, $config);
        }

        return $upload;
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

        // Set file visibility
        if ($config['is_public']) {
            $disk->setVisibility($filePath, 'public');
        } else {
            $disk->setVisibility($filePath, 'private');
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
     * @return Upload
     */
    protected function createUploadRecord(UploadedFile $file, string $filePath, array $fileInfo, array $config): Upload
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

        return Upload::query()->create([
            'user_id' => Auth::id(),
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
     * @param \Intervention\Image\Interfaces\ImageInterface $image
     */
    protected function generateThumbnails(Upload $upload, $image): void
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
     * Delete a file
     *
     * @param Upload $upload
     * @return bool
     */
    public function delete(Upload $upload): bool
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
        try {
            $storage = Storage::disk($upload->disk);

            if (method_exists($storage, 'temporaryUrl')) {
                return $storage->temporaryUrl($upload->file_path, $expiration);
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
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
        if (!$upload->isImage() || !isset($upload->metadata['thumbnails'][$size])) {
            return null;
        }

        $thumbnailPath = $upload->metadata['thumbnails'][$size];

        try {
            return Storage::disk($upload->disk)->url($thumbnailPath);
        } catch (Exception $e) {
            return null;
        }
    }
}
