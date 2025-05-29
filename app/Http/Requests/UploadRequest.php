<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * Request validation for file uploads.
 */
class UploadRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /**
             * The file to upload.
             *
             * @var UploadedFile $file
             */
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB default
            ],

            /**
             * The storage disk to use.
             *
             * @var string|null $disk
             * @example "public"
             */
            'disk' => [
                'nullable',
                'string',
                Rule::in(['local', 'public', 's3', 'ftp', 'sftp']),
            ],

            /**
             * The folder to store the file in.
             *
             * @var string|null $folder
             * @example "uploads/profiles"
             */
            'folder' => ['nullable', 'string', 'max:255'],

            /**
             * Whether the file should be publicly accessible.
             *
             * @var bool|null $is_public
             * @example true
             */
            'is_public' => ['nullable', 'boolean'],

            /**
             * Whether to generate thumbnails for images.
             *
             * @var bool|null $generate_thumbnails
             * @example true
             */
            'generate_thumbnails' => ['nullable', 'boolean'],

            /**
             * Maximum file size in bytes.
             *
             * @var int|null $max_file_size
             * @example 5242880
             */
            'max_file_size' => ['nullable', 'integer', 'min:1'],

            /**
             * Allowed file extensions.
             *
             * @var array|null $allowed_extensions
             * @example ["jpg", "png", "pdf"]
             */
            'allowed_extensions' => ['nullable', 'array'],
            'allowed_extensions.*' => ['string'],

            /**
             * Image quality for compression (1-100).
             *
             * @var int|null $image_quality
             * @example 90
             */
            'image_quality' => ['nullable', 'integer', 'min:1', 'max:100'],

            /**
             * Maximum image width in pixels.
             *
             * @var int|null $image_max_width
             * @example 2048
             */
            'image_max_width' => ['nullable', 'integer', 'min:1'],

            /**
             * Maximum image height in pixels.
             *
             * @var int|null $image_max_height
             * @example 2048
             */
            'image_max_height' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'A file is required for upload.',
            'file.file' => 'The uploaded file is not valid.',
            'file.max' => 'The file size must not exceed 10MB.',
            'disk.in' => 'Invalid storage disk specified.',
            'image_quality.between' => 'Image quality must be between 1 and 100.',
        ];
    }
}
