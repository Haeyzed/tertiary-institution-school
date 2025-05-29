<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Request validation for parent operations.
 *
 * Handles validation for creating and updating parent records.
 */
class ParentRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /**
             * The full name of the parent.
             *
             * Optional full name of the parent/guardian.
             * @var string|null $name
             * @example "John Smith"
             */
            'name' => ['nullable', 'string', 'max:255'],

            /**
             * The email address of the parent.
             *
             * Must be unique and a valid email format.
             * @var string|null $email
             * @example "john.smith@email.com"
             */
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->parent->user_id ?? null)],

            /**
             * The password for the parent's account.
             *
             * Must be at least 8 characters long.
             * @var string|null $password
             * @example "securepassword123"
             */
            'password' => ['nullable', 'string', 'min:8'],

            /**
             * The phone number of the parent.
             *
             * Contact phone number for the parent.
             * @var string|null $phone
             * @example "+1234567890"
             */
            'phone' => ['nullable', 'string', 'max:20'],

            /**
             * The gender of the parent.
             *
             * Must be one of the predefined gender options.
             * @var string|null $gender
             * @example "male"
             */
            'gender' => ['nullable', 'string', Rule::in(GenderEnum::values())],

            /**
             * The residential address of the parent.
             *
             * Full address information.
             * @var string|null $address
             * @example "123 Main Street, City, State, ZIP"
             */
            'address' => ['nullable', 'string'],

            /**
             * The occupation of the parent.
             *
             * Professional occupation or job title.
             * @var string|null $occupation
             * @example "Software Engineer"
             */
            'occupation' => ['nullable', 'string', 'max:100'],

            /**
             * The relationship to the student.
             *
             * How the parent is related to the student.
             * @var string|null $relationship
             * @example "Father"
             */
            'relationship' => ['nullable', 'string', 'max:50'],
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
            'email.unique' => 'This email address is already in use.',
            'gender.in' => 'Gender must be male, female, or other.',
        ];
    }
}
