<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Request validation for password change.
 *
 * Handles validation for changing user password while authenticated.
 */
class ChangePasswordRequest extends BaseRequest
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
             * The current password of the user.
             *
             * User's existing password for verification.
             * @var string $current_password
             * @example "currentpassword123"
             */
            'current_password' => ['required', 'string'],

            /**
             * The new password.
             *
             * Must be at least 8 characters, confirmed, and different from current password.
             * @var string $new_password
             * @example "newpassword123"
             */
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
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
            'current_password.required' => 'Current password is required',
            'new_password.required' => 'New password is required',
            'new_password.min' => 'New password must be at least 8 characters',
            'new_password.confirmed' => 'New password confirmation does not match',
            'new_password.different' => 'New password must be different from current password',
        ];
    }
}
