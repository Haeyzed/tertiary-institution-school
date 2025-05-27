<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Enums\GenderEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Request validation for profile updates.
 *
 * Handles validation for updating authenticated user's profile information.
 */
class UpdateProfileRequest extends BaseRequest
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
             * Optional field for updating user's display name.
             * @var string|null $name
             * @example "John Smith"
             */
            'name' => ['sometimes', 'string', 'max:255'],

            /**
             * The email address of the user.
             *
             * Must be unique and a valid email format, excluding current user.
             * @var string|null $email
             * @example "john.smith@example.com"
             */
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore(Auth::id()),
            ],

            /**
             * The phone number of the user.
             *
             * Optional contact phone number for the user.
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
             * Optional full address information for the user.
             * @var string|null $address
             * @example "123 Main Street, City, State, ZIP Code"
             */
            'address' => ['nullable', 'string'],

            /**
             * The profile photo path or URL.
             *
             * Optional reference to user's profile picture file.
             * @var string|null $photo
             * @example "/uploads/profiles/user123.jpg"
             */
            'photo' => ['nullable', 'string'],
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
            'name.string' => 'Name must be a string',
            'name.max' => 'Name cannot exceed 255 characters',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'This email is already registered',
            'email.max' => 'Email cannot exceed 255 characters',
            'phone.max' => 'Phone number cannot exceed 20 characters',
            'gender.in' => 'Gender must be male, female, or other',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'phone' => 'phone number',
            'gender' => 'gender',
            'address' => 'address',
            'photo' => 'profile photo',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from string fields
        $this->merge([
            'name' => $this->name ? trim($this->name) : $this->name,
            'email' => $this->email ? trim(strtolower($this->email)) : $this->email,
            'phone' => $this->phone ? trim($this->phone) : $this->phone,
            'address' => $this->address ? trim($this->address) : $this->address,
        ]);
    }
}
