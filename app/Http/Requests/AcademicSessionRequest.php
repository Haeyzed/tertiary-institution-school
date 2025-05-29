<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class AcademicSessionRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
             * The name of the academic session.
             *
             * A human-readable label.
             * @var string $name
             * @example "2024/2025 Academic Session"
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * The start date of the academic session.
             *
             * Must be a valid date format.
             * @var string $start_date
             * @example "2024-09-01"
             */
            'start_date' => ['required', 'date'],

            /**
             * The end date of the academic session.
             *
             * Must be a valid date and after the start date.
             * @var string $end_date
             * @example "2025-06-30"
             */
            'end_date' => ['required', 'date', 'after:start_date'],

            /**
             * Indicates if this is the current academic session.
             *
             * Optional boolean flag.
             * @var bool|null $is_current
             * @example true
             */
            'is_current' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'end_date.after' => 'The end date must be after the start date.',
        ];
    }
}
