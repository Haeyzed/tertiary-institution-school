<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Request validation for course operations.
 *
 * Handles validation for creating and updating courses.
 */
class CourseRequest extends BaseRequest
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
             * The name of the course.
             *
             * A descriptive name for the course.
             * @var string $name
             * @example "Data Structures and Algorithms"
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * The unique course code.
             *
             * A short identifier for the course, must be unique.
             * @var string $code
             * @example "CS201"
             */
            'code' => ['required', 'string', 'max:50', Rule::unique('courses', 'code')->ignore($this->route('course'))],

            /**
             * Optional description of the course.
             *
             * Detailed information about the course content and objectives.
             * @var string|null $description
             * @example "This course covers fundamental data structures and algorithms used in computer science."
             */
            'description' => ['nullable', 'string'],

            /**
             * The number of credit hours for the course.
             *
             * Must be at least 1 credit hour.
             * @var int $credit_hours
             * @example 3
             */
            'credit_hours' => ['required', 'integer', 'min:1'],

            /**
             * The ID of the department offering the course.
             *
             * Must reference an existing department.
             * @var int $department_id
             * @example 1
             */
            'department_id' => ['required', 'integer', 'exists:departments,id'],
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
            'code.unique' => 'This course code is already in use.',
            'department_id.exists' => 'The selected department does not exist.',
            'credit_hours.min' => 'Credit hours must be at least 1.',
        ];
    }
}
