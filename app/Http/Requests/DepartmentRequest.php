<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends BaseRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /**
             * The name of the department.
             *
             * A string with a maximum length of 255 characters.
             * @var string $name
             * @example "Department of Computer Science"
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * The code of the department.
             *
             * A short unique identifier with a maximum of 50 characters.
             * @var string $code
             * @example "CSC"
             */
            'code' => ['required', 'string', 'max:50', Rule::unique('departments', 'code')->ignore($this->route('department'))],

            /**
             * The ID of the faculty this department belongs to.
             *
             * Must exist in the `faculties` table.
             * @var int $faculty_id
             * @example 1
             */
            'faculty_id' => ['required', 'integer', 'exists:faculties,id'],
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
            'code.unique' => 'This department code is already in use.',
            'faculty_id.exists' => 'The selected faculty does not exist.',
        ];
    }
}
