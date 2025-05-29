<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Request validation for result operations.
 *
 * Handles validation for creating and updating student exam results.
 */
class ResultRequest extends BaseRequest
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
             * The ID of the student whose result is being recorded.
             *
             * Must reference an existing student.
             * @var int $student_id
             * @example 1
             */
            'student_id' => ['required', 'integer', 'exists:students,id'],

            /**
             * The ID of the course for which the result is recorded.
             *
             * Must reference an existing course.
             * @var int $course_id
             * @example 1
             */
            'course_id' => ['required', 'integer', 'exists:courses,id'],

            /**
             * The ID of the exam for which the result is recorded.
             *
             * Must reference an existing exam.
             * @var int $exam_id
             * @example 1
             */
            'exam_id' => ['required', 'integer', 'exists:exams,id'],

            /**
             * The ID of the semester when the exam was taken.
             *
             * Must reference an existing semester.
             * @var int $semester_id
             * @example 1
             */
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],

            /**
             * The score achieved by the student.
             *
             * Must be between 0 and 100.
             * @var float $score
             * @example 85.5
             */
            'score' => ['required', 'numeric', 'min:0', 'max:100'],

            /**
             * The ID of the grade assigned based on the score.
             *
             * Optional reference to a grade record.
             * @var int|null $grade_id
             * @example 1
             */
            'grade_id' => ['nullable', 'integer', 'exists:grades,id'],

            /**
             * Additional remarks or comments about the result.
             *
             * Optional notes about the student's performance.
             * @var string|null $remarks
             * @example "Excellent performance in practical section"
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
            'course_id.exists' => 'The selected course does not exist.',
            'exam_id.exists' => 'The selected exam does not exist.',
            'semester_id.exists' => 'The selected semester does not exist.',
            'grade_id.exists' => 'The selected grade does not exist.',
            'score.min' => 'Score cannot be negative.',
            'score.max' => 'Score cannot exceed 100.',
        ];
    }
}
