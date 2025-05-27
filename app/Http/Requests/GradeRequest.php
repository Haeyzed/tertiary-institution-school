<?php

namespace App\Http\Requests;

/**
 * Request validation for grade operations.
 *
 * Handles validation for creating and updating grade scales.
 */
class GradeRequest extends BaseRequest
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
             * The grade letter or symbol.
             *
             * A short representation of the grade (e.g., A, B+, C).
             * @var string $grade
             * @example "A"
             */
            'grade' => ['required', 'string', 'max:10'],

            /**
             * The minimum score for this grade.
             *
             * Must be between 0-100 and less than or equal to max_score.
             * @var float $min_score
             * @example 90.0
             */
            'min_score' => ['required', 'numeric', 'min:0', 'max:100', 'lte:max_score'],

            /**
             * The maximum score for this grade.
             *
             * Must be between 0-100 and greater than or equal to min_score.
             * @var float $max_score
             * @example 100.0
             */
            'max_score' => ['required', 'numeric', 'min:0', 'max:100', 'gte:min_score'],

            /**
             * Optional remark or description for the grade.
             *
             * Additional information about the grade level.
             * @var string|null $remark
             * @example "Excellent performance"
             */
            'remark' => ['nullable', 'string', 'max:255'],
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
            'min_score.lte' => 'The minimum score must be less than or equal to the maximum score.',
            'max_score.gte' => 'The maximum score must be greater than or equal to the minimum score.',
        ];
    }
}
