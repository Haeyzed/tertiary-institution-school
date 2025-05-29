<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SemesterRequest extends FormRequest
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
             * The name of the semester.
             *
             * A descriptive label of the semester.
             * @var string $name
             * @example "First Semester"
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * The start date of the semester.
             *
             * Must be a valid date.
             * @var string $start_date
             * @example "2024-09-01"
             */
            'start_date' => ['required', 'date'],

            /**
             * The end date of the semester.
             *
             * Must be a date after the start date.
             * @var string $end_date
             * @example "2025-01-15"
             */
            'end_date' => ['required', 'date', 'after:start_date'],

            /**
             * The ID of the associated academic session.
             *
             * Must exist in the academic_sessions table.
             * @var int $academic_session_id
             * @example 1
             */
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
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
            'end_date.after' => 'The end date must be after the start date.',
            'academic_session_id.exists' => 'The selected academic session does not exist.',
        ];
    }
}
