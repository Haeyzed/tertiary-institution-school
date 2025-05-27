<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use Illuminate\Validation\Rule;

/**
 * Request validation for staff operations.
 *
 * Handles validation for creating and updating staff records.
 */
class StaffRequest extends BaseRequest
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
             * The full name of the staff member.
             *
             * Optional full name of the staff member.
             * @var string|null $name
             * @example "Dr. Jane Smith"
             */
            'name' => ['nullable', 'string', 'max:255'],

            /**
             * The email address of the staff member.
             *
             * Must be unique and a valid email format.
             * @var string|null $email
             * @example "jane.smith@university.edu"
             */
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->staff->user_id ?? null)],

            /**
             * The password for the staff member's account.
             *
             * Must be at least 8 characters long.
             * @var string|null $password
             * @example "securepassword123"
             */
            'password' => ['nullable', 'string', 'min:8'],

            /**
             * The phone number of the staff member.
             *
             * Contact phone number.
             * @var string|null $phone
             * @example "+1234567890"
             */
            'phone' => ['nullable', 'string', 'max:20'],

            /**
             * The gender of the staff member.
             *
             * Must be one of the predefined gender options.
             * @var string|null $gender
             * @example "female"
             */
            'gender' => ['nullable', 'string', Rule::in(GenderEnum::values())],

            /**
             * The residential address of the staff member.
             *
             * Full address information.
             * @var string|null $address
             * @example "123 University Avenue, City, State, ZIP"
             */
            'address' => ['nullable', 'string'],

            /**
             * The unique staff identification number.
             *
             * Must be unique across all staff members.
             * @var string $staff_id
             * @example "STAFF001"
             */
            'staff_id' => ['required', 'string', 'max:50', Rule::unique('staff')->ignore($this->route('staff'))],

            /**
             * The ID of the department the staff member belongs to.
             *
             * Must reference an existing department.
             * @var int $department_id
             * @example 1
             */
            'department_id' => ['required', 'integer', 'exists:departments,id'],

            /**
             * The position or job title of the staff member.
             *
             * Optional job title or position.
             * @var string|null $position
             * @example "Associate Professor"
             */
            'position' => ['nullable', 'string', 'max:100'],

            /**
             * The date when the staff member joined the institution.
             *
             * Optional joining date.
             * @var string|null $joining_date
             * @example "2020-09-01"
             */
            'joining_date' => ['nullable', 'date'],
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
            'staff_id.unique' => 'This staff ID is already in use.',
            'email.unique' => 'This email address is already in use.',
            'department_id.exists' => 'The selected department does not exist.',
            'gender.in' => 'Gender must be male, female, or other.',
        ];
    }
}
