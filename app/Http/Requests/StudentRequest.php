<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use App\Enums\StudentStatusEnum;
use Illuminate\Validation\Rule;

/**
 * Request validation for student operations.
 *
 * Handles validation for creating and updating student records.
 */
class StudentRequest extends BaseRequest
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
             * The full name of the student.
             *
             * Optional full name of the student.
             * @var string|null $name
             * @example "John Doe"
             */
            'name' => ['nullable', 'string', 'max:255'],

            /**
             * The email address of the student.
             *
             * Must be unique and a valid email format.
             * @var string|null $email
             * @example "john.doe@student.university.edu"
             */
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->student->user_id ?? null)],

            /**
             * The password for the student's account.
             *
             * Must be at least 8 characters long.
             * @var string|null $password
             * @example "securepassword123"
             */
            'password' => ['nullable', 'string', 'min:8'],

            /**
             * The phone number of the student.
             *
             * Contact phone number.
             * @var string|null $phone
             * @example "+1234567890"
             */
            'phone' => ['nullable', 'string', 'max:20'],

            /**
             * The gender of the student.
             *
             * Must be one of the predefined gender options.
             * @var string|null $gender
             * @example "male"
             */
            'gender' => ['nullable', 'string', Rule::in(GenderEnum::values())],

            /**
             * The residential address of the student.
             *
             * Full address information.
             * @var string|null $address
             * @example "456 Student Housing, University Campus"
             */
            'address' => ['nullable', 'string'],

            /**
             * The unique student identification number.
             *
             * Must be unique across all students.
             * @var string $student_id
             * @example "STU2024001"
             */
            'student_id' => ['required', 'string', 'max:50', Rule::unique('students')->ignore($this->route('student'))],

            /**
             * The ID of the program the student is enrolled in.
             *
             * Must reference an existing program.
             * @var int $program_id
             * @example 1
             */
            'program_id' => ['required', 'integer', 'exists:programs,id'],

            /**
             * The ID of the student's parent or guardian.
             *
             * Optional reference to a parent record.
             * @var int|null $parent_id
             * @example 1
             */
            'parent_id' => ['nullable', 'integer', 'exists:parents,id'],

            /**
             * The date when the student was admitted.
             *
             * Must be a valid date.
             * @var string $admission_date
             * @example "2024-09-01"
             */
            'admission_date' => ['required', 'date'],

            /**
             * The current semester the student is in.
             *
             * Must be at least 1 if provided.
             * @var int|null $current_semester
             * @example 3
             */
            'current_semester' => ['nullable', 'integer', 'min:1'],

            /**
             * The current status of the student.
             *
             * Must be one of the predefined student statuses.
             * @var string|null $status
             * @example "active"
             */
            'status' => ['nullable', 'string', Rule::in(StudentStatusEnum::values())],
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
            'student_id.unique' => 'This student ID is already in use.',
            'email.unique' => 'This email address is already in use.',
            'program_id.exists' => 'The selected program does not exist.',
            'parent_id.exists' => 'The selected parent does not exist.',
            'status.in' => 'Status must be active, inactive, graduated, or suspended.',
        ];
    }
}
