<?php

namespace App\Http\Requests;

/**
 * Request validation for fee operations.
 *
 * Handles validation for creating and updating fees.
 */
class FeeRequest extends BaseRequest
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
             * The name of the fee.
             *
             * A descriptive name for the fee type.
             * @var string $name
             * @example "Tuition Fee"
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * The amount of the fee.
             *
             * Must be a positive number representing the fee amount.
             * @var float $amount
             * @example 5000.00
             */
            'amount' => ['required', 'numeric', 'min:0'],

            /**
             * The ID of the program this fee applies to.
             *
             * Must reference an existing program.
             * @var int $program_id
             * @example 1
             */
            'program_id' => ['required', 'integer', 'exists:programs,id'],

            /**
             * The ID of the semester this fee applies to.
             *
             * Optional reference to a specific semester.
             * @var int|null $semester_id
             * @example 1
             */
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],

            /**
             * Optional description of the fee.
             *
             * Additional details about what the fee covers.
             * @var string|null $description
             * @example "Annual tuition fee for undergraduate programs"
             */
            'description' => ['nullable', 'string'],
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
            'program_id.exists' => 'The selected program does not exist.',
            'semester_id.exists' => 'The selected semester does not exist.',
            'amount.min' => 'Amount cannot be negative.',
        ];
    }
}
