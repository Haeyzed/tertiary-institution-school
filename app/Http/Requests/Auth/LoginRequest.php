<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserTypeEnum;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation for user login.
 */
class LoginRequest extends BaseRequest
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
             * The email address of the user.
             *
             * @var string $email
             * @example "user@example.com"
             */
            'email' => ['required', 'string', 'email', 'max:255'],

            /**
             * The password for the user account.
             *
             * @var string $password
             * @example "password123"
             */
            'password' => ['required', 'string'],

            /**
             * The user type for authentication.
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
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'password.required' => 'Password is required.',
            'user_type.in' => 'Invalid user type provided.',
        ];
    }
}
