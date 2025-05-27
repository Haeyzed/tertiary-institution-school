<?php

namespace App\Http\Requests;

/**
 * Request validation for student assignment submission operations.
 *
 * Handles validation for creating and updating student assignment submissions.
 */
class StudentAssignmentRequest extends BaseRequest
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
             * The ID of the student submitting the assignment.
             *
             * Must reference an existing student.
             * @var int $student_id
             * @example 1
             */
            'student_id' => ['required', 'integer', 'exists:students,id'],

            /**
             * The ID of the assignment being submitted.
             *
             * Must reference an existing assignment.
             * @var int $assignment_id
             * @example 1
             */
            'assignment_id' => ['required', 'integer', 'exists:assignments,id'],

            /**
             * The submission content or file path.
             *
             * Optional text content or file reference for the submission.
             * @var string|null $submission
             * @example "Solution to the binary search tree implementation..."
             */
            'submission' => ['nullable', 'string'],

            /**
             * The date when the assignment was submitted.
             *
             * Optional submission timestamp.
             * @var string|null $submission_date
             * @example "2024-12-10 14:30:00"
             */
            'submission_date' => ['nullable', 'date'],

            /**
             * The score awarded for the assignment.
             *
             * Must be a positive number if provided.
             * @var float|null $score
             * @example 85.0
             */
            'score' => ['nullable', 'numeric', 'min:0'],

            /**
             * Additional remarks or feedback on the submission.
             *
             * Optional comments from the instructor.
             * @var string|null $remarks
             * @example "Good implementation, but could improve code documentation"
             */
            'remarks' => ['nullable', 'string'],
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
            'student_id.exists' => 'The selected student does not exist.',
            'assignment_id.exists' => 'The selected assignment does not exist.',
            'score.min' => 'Score cannot be negative.',
        ];
    }
}
