<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

/**
 * Request validation for password reset request.
 *
 * Handles validation for initiating password reset process.
 */
class ForgotPasswordRequest extends BaseRequest
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
             * The email address for password reset.
             *
             * Must exist in the users table.
             * @var string $email
             * @example "user@example.com"
             */
            'email' => ['required', 'string', 'email', 'exists:users,email'],
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
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email address',
            'email.exists' => 'No account found with this email address',
        ];
    }
}
