<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserTypeEnum;
use App\Http\Requests\BaseRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Request validation for forgot password.
 */
class ForgotPasswordRequest extends BaseRequest
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
             * The email address of the user.
             *
             * @var string $email
             * @example "user@example.com"
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

                    if (!$query->exists()) {
                        $fail('We could not find a user with that email address and user type.');
                    }
                }
            ],

            /**
             * The user type for password reset.
             *
             * @var string|null $user_type
             * @example "student"
             */
            'user_type' => ['nullable', 'string', Rule::in(UserTypeEnum::values())],
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
            'user_type.in' => 'Invalid user type provided.',
        ];
    }
}
