<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class AssignRolesRequest extends BaseRequest
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
             * An array of role IDs to be assigned.
             *
             * Must contain valid role IDs.
             * @var array $roles
             * @example [1, 2, 3]
             */
            'roles' => 'required|array',

            /**
             * Each item must be a valid role ID.
             *
             * Checks existence in the roles table.
             * @var int $roles .*
             * @example 1
             */
            'roles.*' => 'exists:roles,id',
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
            'roles.required' => 'At least one role must be selected.',
            'roles.*.exists' => 'One or more selected roles do not exist.',
        ];
    }
}
