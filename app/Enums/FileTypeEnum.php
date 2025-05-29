<?php

namespace App\Enums;

enum FileTypeEnum: string
{
    case IMAGE = 'image';
    case DOCUMENT = 'document';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case ARCHIVE = 'archive';
    case OTHER = 'other';

    /**
     * Get all file type values as an array
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable name for the file type
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::IMAGE => 'Image',
            self::DOCUMENT => 'Document',
            self::VIDEO => 'Video',
            self::AUDIO => 'Audio',
            self::ARCHIVE => 'Archive',
            self::OTHER => 'Other',
        };
    }

    /**
     * Get file type from MIME type
     *
     * @param string $mimeType
     * @return self
     */
    public static function fromMimeType(string $mimeType): self
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => self::IMAGE,
            str_starts_with($mimeType, 'video/') => self::VIDEO,
            str_starts_with($mimeType, 'audio/') => self::AUDIO,
            in_array($mimeType, [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'text/csv',
                'application/rtf',
            ]) => self::DOCUMENT,
            in_array($mimeType, [
                'application/zip',
                'application/x-rar-compressed',
                'application/x-7z-compressed',
                'application/x-tar',
                'application/gzip',
            ]) => self::ARCHIVE,
            default => self::OTHER,
        };
    }

    /**
     * Get allowed MIME types for this file type
     *
     * @return array<string>
     */
    public function getAllowedMimeTypes(): array
    {
        return match($this) {
            self::IMAGE => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/svg+xml',
                'image/bmp',
                'image/tiff',
            ],
            self::DOCUMENT => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'text/csv',
                'application/rtf',
            ],
            self::VIDEO => [
                'video/mp4',
                'video/avi',
                'video/quicktime',
                'video/x-msvideo',
                'video/webm',
                'video/ogg',
            ],
            self::AUDIO => [
                'audio/mpeg',
                'audio/wav',
                'audio/ogg',
                'audio/mp4',
                'audio/webm',
                'audio/flac',
            ],
            self::ARCHIVE => [
                'application/zip',
                'application/x-rar-compressed',
                'application/x-7z-compressed',
                'application/x-tar',
                'application/gzip',
            ],
            self::OTHER => [],
        };
    }
}
