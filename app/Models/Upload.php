<?php

namespace App\Models;

use App\Enums\FileTypeEnum;
use DateTimeInterface;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Log;

class Upload extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'original_name',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'disk',
        'folder',
        'is_public',
        'metadata',
        'uploaded_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_type' => FileTypeEnum::class,
            'metadata' => 'array',
            'is_public' => 'boolean',
            'uploaded_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the upload.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include uploads by a specific user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include uploads of a specific file type.
     */
    public function scopeByFileType($query, FileTypeEnum $fileType)
    {
        return $query->where('file_type', $fileType);
    }

    /**
     * Scope a query to only include uploads from a specific disk.
     */
    public function scopeByDisk($query, string $disk)
    {
        return $query->where('disk', $disk);
    }

    /**
     * Scope a query to only include public uploads.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get the public URL for the file.
     */
    public function getPublicUrlAttribute(): ?string
    {
        if (!$this->fileExists()) {
            return null;
        }

        try {
            $storage = Storage::disk($this->disk);

            // For S3 and other cloud storage, use the url() method
            if (in_array($this->disk, ['s3', 'spaces', 'gcs'])) {
                return $storage->url($this->file_path);
            }

            // For local storage, check if it's public
            if ($this->is_public) {
                return $storage->url($this->file_path);
            }

            return null;
        } catch (Exception $e) {
            Log::error('Failed to generate public URL for upload: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the download URL for the file.
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('uploads.download', $this->id);
    }

    /**
     * Get human-readable file size.
     */
    public function getHumanFileSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->file_size;

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if the file exists on disk.
     */
    public function fileExists(): bool
    {
        try {
            return Storage::disk($this->disk)->exists($this->file_path);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete the file from storage.
     */
    public function deleteFile(): bool
    {
        try {
            if ($this->fileExists()) {
                return Storage::disk($this->disk)->delete($this->file_path);
            }
            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the file content.
     */
    public function getFileContent(): ?string
    {
        try {
            if ($this->fileExists()) {
                return Storage::disk($this->disk)->get($this->file_path);
            }
            return null;
        } catch (Exception $e) {
            Log::error('Failed to get file content: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if the file is an image.
     */
    public function isImage(): bool
    {
        return $this->file_type === FileTypeEnum::IMAGE;
    }

    /**
     * Check if the file is a document.
     */
    public function isDocument(): bool
    {
        return $this->file_type === FileTypeEnum::DOCUMENT;
    }

    /**
     * Check if the file is a video.
     */
    public function isVideo(): bool
    {
        return $this->file_type === FileTypeEnum::VIDEO;
    }

    /**
     * Check if the file is audio.
     */
    public function isAudio(): bool
    {
        return $this->file_type === FileTypeEnum::AUDIO;
    }

    /**
     * Check if the file is an archive.
     */
    public function isArchive(): bool
    {
        return $this->file_type === FileTypeEnum::ARCHIVE;
    }

    /**
     * Get thumbnail URL for a specific size.
     */
    public function getThumbnailUrl(string $size = 'thumb'): ?string
    {
        if (!$this->isImage() || !isset($this->metadata['thumbnails'][$size])) {
            return null;
        }

        $thumbnailPath = $this->metadata['thumbnails'][$size];

        try {
            $storage = Storage::disk($this->disk);

            // For S3 and other cloud storage, use the url() method
            if (in_array($this->disk, ['s3', 'spaces', 'gcs'])) {
                return $storage->url($thumbnailPath);
            }

            // For local storage
            return $storage->url($thumbnailPath);
        } catch (Exception $e) {
            Log::error('Failed to generate thumbnail URL: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all available thumbnail URLs.
     */
    public function getThumbnailUrls(): array
    {
        if (!$this->isImage() || !isset($this->metadata['thumbnails'])) {
            return [];
        }

        $thumbnails = [];
        foreach ($this->metadata['thumbnails'] as $size => $path) {
            $thumbnails[$size] = $this->getThumbnailUrl($size);
        }

        return array_filter($thumbnails); // Remove null values
    }

    /**
     * Generate a temporary URL for private files.
     */
    public function getTemporaryUrl(DateTimeInterface $expiration): ?string
    {
        try {
            $storage = Storage::disk($this->disk);

            if (method_exists($storage, 'temporaryUrl')) {
                return $storage->temporaryUrl($this->file_path, $expiration);
            }

            return null;
        } catch (Exception $e) {
            Log::error('Failed to generate temporary URL: ' . $e->getMessage());
            return null;
        }
    }
}
