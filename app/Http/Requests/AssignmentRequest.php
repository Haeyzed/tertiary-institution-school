<?php

namespace App\Http\Requests;

/**
 * Request validation for assignment operations.
 *
 * Handles validation for creating and updating assignments.
 */
class AssignmentRequest extends BaseRequest
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
             * The title of the assignment.
             *
             * A descriptive title for the assignment.
             * @var string $title
             * @example "Data Structures and Algorithms Assignment 1"
             */
            'title' => ['required', 'string', 'max:255'],

            /**
             * The detailed description of the assignment.
             *
             * Instructions and requirements for the assignment.
             * @var string $description
             * @example "Implement a binary search tree with insert, delete, and search operations."
             */
            'description' => ['required', 'string'],

            /**
             * The ID of the course this assignment belongs to.
             *
             * Must reference an existing course.
             * @var int $course_id
             * @example 1
             */
            'course_id' => ['required', 'integer', 'exists:courses,id'],

            /**
             * The ID of the semester this assignment is for.
             *
             * Must reference an existing semester.
             * @var int $semester_id
             * @example 1
             */
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],

            /**
             * The ID of the staff member who created the assignment.
             *
             * Must reference an existing staff member.
             * @var int $staff_id
             * @example 1
             */
            'staff_id' => ['required', 'integer', 'exists:staff,id'],

            /**
             * The due date for the assignment submission.
             *
             * Must be a valid date format.
             * @var string $due_date
             * @example "2024-12-15"
             */
            'due_date' => ['required', 'date'],

            /**
             * The total marks/points for the assignment.
             *
             * Must be a positive number.
             * @var float $total_marks
             * @example 100.0
             */
            'total_marks' => ['required', 'numeric', 'min:0'],

            /**
             * Optional attachment file path or URL.
             *
             * Reference to any attached files for the assignment.
             * @var string|null $attachment
             * @example "/uploads/assignments/assignment1.pdf"
             */
            'attachment' => ['nullable', 'string'],
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
            'course_id.exists' => 'The selected course does not exist.',
            'semester_id.exists' => 'The selected semester does not exist.',
            'staff_id.exists' => 'The selected staff does not exist.',
            'total_marks.min' => 'Total marks cannot be negative.',
        ];
    }
}
