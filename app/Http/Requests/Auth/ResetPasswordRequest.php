<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Request validation for password reset completion.
 *
 * Handles validation for completing the password reset process.
 */
class ResetPasswordRequest extends BaseRequest
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
             * The password reset token.
             *
             * Token received via email for password reset.
             * @var string $token
             * @example "abc123def456ghi789"
             */
            'token' => ['required', 'string'],

            /**
             * The email address for password reset.
             *
             * Must exist in the users table.
             * @var string $email
             * @example "user@example.com"
             */
            'email' => ['required', 'string', 'email', 'exists:users,email'],

            /**
             * The new password.
             *
             * Must be at least 8 characters and confirmed.
             * @var string $password
             * @example "newsecurepassword123"
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
            'token.required' => 'Token is required',
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email address',
            'email.exists' => 'No account found with this email address',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
        ];
    }
}
