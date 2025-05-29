<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Request validation for profile photo upload.
 */
class ProfilePhotoRequest extends BaseRequest
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
             * The profile photo file.
             *
             * @var UploadedFile $photo
             */
            'photo' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5MB
                'dimensions:min_width=100,min_height=100,max_width=2048,max_height=2048',
            ],
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
            'photo.required' => 'A profile photo is required.',
            'photo.image' => 'The file must be an image.',
            'photo.mimes' => 'The image must be a JPEG, JPG, PNG, or WebP file.',
            'photo.max' => 'The image size must not exceed 5MB.',
            'photo.dimensions' => 'The image must be at least 100x100 pixels and no larger than 2048x2048 pixels.',
        ];
    }
}
