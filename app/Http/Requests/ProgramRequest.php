<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Request validation for program operations.
 *
 * Handles validation for creating and updating academic programs.
 */
class ProgramRequest extends BaseRequest
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
             * The name of the academic program.
             *
             * A descriptive name for the program.
             * @var string $name
             * @example "Bachelor of Science in Computer Science"
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * The unique program code.
             *
             * A short identifier for the program, must be unique.
             * @var string $code
             * @example "BSCS"
             */
            'code' => ['required', 'string', 'max:50', Rule::unique('programs')->ignore($this->route('program'))],

            /**
             * The ID of the department offering the program.
             *
             * Must reference an existing department.
             * @var int $department_id
             * @example 1
             */
            'department_id' => ['required', 'integer', 'exists:departments,id'],

            /**
             * The duration of the program in years.
             *
             * Must be at least 1 year.
             * @var int $duration
             * @example 4
             */
            'duration' => ['required', 'integer', 'min:1'],
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
            'code.unique' => 'This program code is already in use.',
            'department_id.exists' => 'The selected department does not exist.',
            'duration.min' => 'Program duration must be at least 1 year.',
        ];
    }
}
