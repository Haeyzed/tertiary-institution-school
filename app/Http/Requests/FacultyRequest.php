<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class FacultyRequest extends BaseRequest
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
             * The name of the faculty.
             *
             * This should be a valid string with a maximum length of 255 characters.
             * @var string $name
             * @example "Faculty of Science"
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * The code of the faculty.
             *
             * This should be a short unique identifier with a maximum length of 50 characters.
             * @var string $code
             * @example "SCI"
             */
            'code' => ['required', 'string', 'max:50', Rule::unique('faculties', 'code')->ignore($this->route('faculty'))],
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
            'code.unique' => 'This faculty code is already in use.',
        ];
    }
}
