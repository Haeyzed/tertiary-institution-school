<?php

namespace App\Http\Requests\Auth;

use App\Enums\GenderEnum;
use App\Http\Requests\BaseRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * Request validation for updating user profile with optional photo upload.
 */
class UpdateProfileRequest extends BaseRequest
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
             * The full name of the user.
             *
             * @var string|null $name
             * @example "Alice Johnson"
             */
            'name' => ['sometimes', 'string', 'max:255'],

            /**
             * The email address of the user.
             *
             * @var string|null $email
             * @example "alice.johnson@example.com"
             */
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    if (!$user) return;

                    $query = User::query()->where('email', $value)
                        ->where('user_type', $user->user_type)
                        ->where('id', '!=', $user->id);

                    if ($query->exists()) {
                        $fail('The email has already been taken for this user type.');
                    }
                }
            ],

            /**
             * The password for the user account.
             *
             * @var string|null $password
             * @example "NewSecurePassword123!"
             */
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],

            /**
             * The phone number of the user.
             *
             * @var string|null $phone
             * @example "+1234567890"
             */
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],

            /**
             * The gender of the user.
             *
             * @var string|null $gender
             * @example "female"
             */
            'gender' => ['sometimes', 'nullable', 'string', Rule::in(GenderEnum::values())],

            /**
             * The residential address of the user.
             *
             * @var string|null $address
             * @example "789 Main Street, City, State, ZIP"
             */
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],

            /**
             * The profile photo file.
             *
             * @var UploadedFile|null $photo
             */
            'photo' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5MB
                'dimensions:min_width=100,min_height=100,max_width=2048,max_height=2048',
            ],

            /**
             * Relations to load with the user.
             *
             * @var array|null $with
             * @example ["roles", "permissions"]
             */
            'with' => ['sometimes', 'array'],
            'with.*' => ['string'],
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
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name must not exceed 255 characters.',
            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email must not exceed 255 characters.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'phone.max' => 'Phone number must not exceed 20 characters.',
            'gender.in' => 'Invalid gender selected.',
            'address.max' => 'Address must not exceed 500 characters.',
            'photo.image' => 'The file must be an image.',
            'photo.mimes' => 'The image must be a JPEG, JPG, PNG, or WebP file.',
            'photo.max' => 'The image size must not exceed 5MB.',
            'photo.dimensions' => 'The image must be at least 100x100 pixels and no larger than 2048x2048 pixels.',
        ];
    }
}
