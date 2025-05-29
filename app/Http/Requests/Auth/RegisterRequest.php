<?php

namespace App\Http\Requests\Auth;

use App\Enums\GenderEnum;
use App\Enums\UserTypeEnum;
use App\Http\Requests\BaseRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * Request validation for user registration with optional photo upload.
 */
class RegisterRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /**
             * The full name of the user.
             *
             * @var string $name
             * @example "Alice Johnson"
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * The email address of the user.
             *
             * @var string $email
             * @example "alice.johnson@example.com"
             */
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $query = User::query()->where('email', $value);

                    if ($this->has('user_type')) {
                        $query->where('user_type', $this->input('user_type'));
                    }

                    if ($query->exists()) {
                        $fail('The email has already been taken for this user type.');
                    }
                }
            ],

            /**
             * The password for the user account.
             *
             * @var string $password
             * @example "SecurePassword123!"
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            /**
             * The phone number of the user.
             *
             * @var string|null $phone
             * @example "+1234567890"
             */
            'phone' => ['nullable', 'string', 'max:20'],

            /**
             * The gender of the user.
             *
             * @var string|null $gender
             * @example "female"
             */
            'gender' => ['nullable', 'string', Rule::in(GenderEnum::values())],

            /**
             * The residential address of the user.
             *
             * @var string|null $address
             * @example "789 Main Street, City, State, ZIP"
             */
            'address' => ['nullable', 'string', 'max:500'],

            /**
             * The profile photo file.
             *
             * @var UploadedFile|null $photo
             */
            'photo' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5MB
                'dimensions:min_width=100,min_height=100,max_width=2048,max_height=2048',
            ],

            /**
             * The user type.
             *
             * @var string|null $user_type
             * @example "student"
             */
            'user_type' => ['nullable', 'string', Rule::in(UserTypeEnum::values())],

            /**
             * Relations to load with the user.
             *
             * @var array|null $with
             * @example ["roles", "permissions"]
             */
            'with' => ['nullable', 'array'],
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
            'name.required' => 'Full name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'phone.max' => 'Phone number must not exceed 20 characters.',
            'gender.in' => 'Invalid gender selected.',
            'address.max' => 'Address must not exceed 500 characters.',
            'photo.image' => 'The file must be an image.',
            'photo.mimes' => 'The image must be a JPEG, JPG, PNG, or WebP file.',
            'photo.max' => 'The image size must not exceed 5MB.',
            'photo.dimensions' => 'The image must be at least 100x100 pixels and no larger than 2048x2048 pixels.',
            'user_type.in' => 'Invalid user type selected.',
        ];
    }
}
