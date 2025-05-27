<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

/**
 * Request validation for user login.
 *
 * Handles validation for user authentication.
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
             * The email address for login.
             *
             * Must be a valid email format.
             * @var string $email
             * @example "user@example.com"
             */
            'email' => ['required', 'string', 'email'],

            /**
             * The password for login.
             *
             * User's account password.
             * @var string $password
             * @example "userpassword123"
             */
            'password' => ['required', 'string'],
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
            'password.required' => 'Password is required',
        ];
    }
}
