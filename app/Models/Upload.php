<?php

namespace App\Models;

use App\Enums\FileTypeEnum;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
     * Get the user that uploaded the file.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the public URL for the file.
     *
     * @return string|null
     */
    public function getPublicUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        try {
            $storage = Storage::disk($this->disk);

            if ($this->is_public) {
                return $storage->url($this->file_path);
            }

            // For private files, generate a temporary URL (if supported)
            if (method_exists($storage, 'temporaryUrl')) {
                return $storage->temporaryUrl($this->file_path, now()->addHours(1));
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get the download URL for the file.
     *
     * @return string
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('uploads.download', $this->id);
    }

    /**
     * Get human-readable file size.
     *
     * @return string
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Delete the file from storage.
     *
     * @return bool
     */
    public function deleteFile(): bool
    {
        if ($this->fileExists()) {
            return Storage::disk($this->disk)->delete($this->file_path);
        }

        return true;
    }

    /**
     * Check if the file exists on disk.
     *
     * @return bool
     */
    public function fileExists(): bool
    {
        return Storage::disk($this->disk)->exists($this->file_path);
    }

    /**
     * Get file content.
     *
     * @return string|null
     */
    public function getFileContent(): ?string
    {
        if ($this->fileExists()) {
            return Storage::disk($this->disk)->get($this->file_path);
        }

        return null;
    }

    /**
     * Check if the file is an image.
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return $this->file_type === FileTypeEnum::IMAGE;
    }

    /**
     * Check if the file is a document.
     *
     * @return bool
     */
    public function isDocument(): bool
    {
        return $this->file_type === FileTypeEnum::DOCUMENT;
    }

    /**
     * Check if the file is a video.
     *
     * @return bool
     */
    public function isVideo(): bool
    {
        return $this->file_type === FileTypeEnum::VIDEO;
    }

    /**
     * Check if the file is an audio file.
     *
     * @return bool
     */
    public function isAudio(): bool
    {
        return $this->file_type === FileTypeEnum::AUDIO;
    }

    /**
     * Scope to filter by user.
     *
     * @param Builder $query
     * @param int $userId
     * @return Builder
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by file type.
     *
     * @param Builder $query
     * @param FileTypeEnum $fileType
     * @return Builder
     */
    public function scopeByFileType(Builder $query, FileTypeEnum $fileType): Builder
    {
        return $query->where('file_type', $fileType);
    }

    /**
     * Scope to filter by disk.
     *
     * @param Builder $query
     * @param string $disk
     * @return Builder
     */
    public function scopeByDisk(Builder $query, string $disk): Builder
    {
        return $query->where('disk', $disk);
    }

    /**
     * Scope to filter public files.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter private files.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('is_public', false);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_public' => 'boolean',
            'metadata' => 'array',
            'uploaded_at' => 'datetime',
            'file_type' => FileTypeEnum::class,
        ];
    }
}
