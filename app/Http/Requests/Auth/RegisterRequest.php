<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Enums\GenderEnum;
use Illuminate\Validation\Rule;

/**
 * Request validation for user registration.
 *
 * Handles validation for new user account creation.
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
             * The full name of the new user.
             *
             * Required full name for account registration.
             * @var string $name
             * @example "John Smith"
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * The email address for the new account.
             *
             * Must be unique and a valid email format.
             * @var string $email
             * @example "john.smith@example.com"
             */
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],

            /**
             * The password for the new account.
             *
             * Must be at least 8 characters and confirmed.
             * @var string $password
             * @example "securepassword123"
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            /**
             * The phone number of the user.
             *
             * Optional contact phone number.
             * @var string|null $phone
             * @example "+1234567890"
             */
            'phone' => ['nullable', 'string', 'max:20'],

            /**
             * The gender of the user.
             *
             * Must be one of the predefined gender options.
             * @var string|null $gender
             * @example "male"
             */
            'gender' => ['nullable', 'string', Rule::in(GenderEnum::options())],

            /**
             * The residential address of the user.
             *
             * Optional address information.
             * @var string|null $address
             * @example "123 Main Street, City, State, ZIP"
             */
            'address' => ['nullable', 'string'],
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
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'This email is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
            'gender.in' => 'Gender must be male, female, or other',
        ];
    }
}
