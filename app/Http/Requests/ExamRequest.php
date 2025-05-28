<?php

namespace App\Http\Requests;

use App\Enums\ExamStatusEnum;
use Illuminate\Validation\Rule;

/**
 * Request validation for exam operations.
 *
 * Handles validation for creating and updating exams.
 */
class ExamRequest extends BaseRequest
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
             * The title of the exam.
             *
             * A descriptive title for the exam.
             * @var string $title
             * @example "Final Exam - Data Structures"
             */
            'title' => ['required', 'string', 'max:255'],

            /**
             * The ID of the course this exam is for.
             *
             * Must reference an existing course.
             * @var int $course_id
             * @example 1
             */
            'course_id' => ['required', 'integer', 'exists:courses,id'],

            /**
             * The ID of the semester this exam is scheduled for.
             *
             * Must reference an existing semester.
             * @var int $semester_id
             * @example 1
             */
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],

            /**
             * The date when the exam will be conducted.
             *
             * Must be a valid date format.
             * @var string $exam_date
             * @example "2024-12-20"
             */
            'exam_date' => ['required', 'date'],

            /**
             * The start time of the exam.
             *
             * Must be in HH:MM format.
             * @var string $start_time
             * @example "09:00"
             */
            'start_time' => ['required', 'date_format:H:i'],

            /**
             * The end time of the exam.
             *
             * Must be in HH:MM format and after start time.
             * @var string $end_time
             * @example "12:00"
             */
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],

            /**
             * The total marks for the exam.
             *
             * Must be a positive number.
             * @var float $total_marks
             * @example 100.0
             */
            'total_marks' => ['required', 'numeric', 'min:0'],

            /**
             * The venue where the exam will be conducted.
             *
             * Optional location information.
             * @var string|null $venue
             * @example "Main Hall A"
             */
            'venue' => ['nullable', 'string', 'max:255'],

            /**
             * The current status of the exam.
             *
             * Must be one of the predefined exam statuses.
             * @var string|null $status
             * @example "scheduled"
             */
            'status' => ['nullable', 'string', Rule::in(ExamStatusEnum::values())],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        // Get available status options dynamically
        $availableStatuses = collect(ExamStatusEnum::cases())
            ->map(fn($enum) => "'{$enum->value}' ({$enum->label()})")
            ->join(', ');
        return [
            'course_id.exists' => 'The selected course does not exist.',
            'semester_id.exists' => 'The selected semester does not exist.',
            'end_time.after' => 'The end time must be after the start time.',
            'total_marks.min' => 'Total marks cannot be negative.',

            'status.required' => 'The exam status is required.',
            'status.string' => 'The exam status must be a valid string.',
            'status.in' => "The selected exam status is invalid. Available options are: {$availableStatuses}.",
        ];
    }
}
