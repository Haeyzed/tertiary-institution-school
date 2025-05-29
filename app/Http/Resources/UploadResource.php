<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UploadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * Unique identifier of the upload.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The original filename.
             *
             * @var string $original_name
             * @example "profile-photo.jpg"
             */
            'original_name' => $this->original_name,

            /**
             * The stored filename.
             *
             * @var string $file_name
             * @example "profile-photo_uuid.jpg"
             */
            'file_name' => $this->file_name,

            /**
             * The file path on disk.
             *
             * @var string $file_path
             * @example "uploads/images/2024/01/profile-photo_uuid.jpg"
             */
            'file_path' => $this->file_path,

            /**
             * The file type.
             *
             * @var string $file_type
             * @example "image"
             */
            'file_type' => $this->file_type->value,

            /**
             * The MIME type.
             *
             * @var string $mime_type
             * @example "image/jpeg"
             */
            'mime_type' => $this->mime_type,

            /**
             * The file size in bytes.
             *
             * @var int $file_size
             * @example 1024000
             */
            'file_size' => $this->file_size,

            /**
             * Human-readable file size.
             *
             * @var string $human_file_size
             * @example "1.02 MB"
             */
            'human_file_size' => $this->human_file_size,

            /**
             * The storage disk.
             *
             * @var string $disk
             * @example "public"
             */
            'disk' => $this->disk,

            /**
             * The folder path.
             *
             * @var string $folder
             * @example "uploads/images/2024/01"
             */
            'folder' => $this->folder,

            /**
             * Whether the file is publicly accessible.
             *
             * @var bool $is_public
             * @example true
             */
            'is_public' => $this->is_public,

            /**
             * The public URL (if available).
             *
             * @var string|null $public_url
             * @example "https://example.com/storage/uploads/images/2024/01/profile-photo_uuid.jpg"
             */
            'public_url' => $this->public_url,

            /**
             * The download URL.
             *
             * @var string $download_url
             * @example "https://api.example.com/uploads/1/download"
             */
            'download_url' => $this->download_url,

            /**
             * File metadata.
             *
             * @var array|null $metadata
             */
            'metadata' => $this->metadata,

            /**
             * Thumbnail URLs (for images).
             *
             * @var array|null $thumbnails
             */
            'thumbnails' => $this->when($this->isImage() && isset($this->metadata['thumbnails']), function () {
                $thumbnails = [];
                foreach ($this->metadata['thumbnails'] ?? [] as $size => $path) {
                    $thumbnails[$size] = route('uploads.thumbnail', ['upload' => $this->id, 'size' => $size]);
                }
                return $thumbnails;
            }),

            /**
             * The uploader information.
             *
             * @var array|null $uploader
             */
            'uploader' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),

            /**
             * Upload timestamp.
             *
             * @var string $uploaded_at
             * @example "2024-01-15T10:30:00Z"
             */
            'uploaded_at' => $this->uploaded_at,

            /**
             * Creation timestamp.
             *
             * @var string $created_at
             * @example "2024-01-15T10:30:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Last update timestamp.
             *
             * @var string $updated_at
             * @example "2024-01-15T10:30:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
