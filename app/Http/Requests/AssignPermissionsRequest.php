<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignPermissionsRequest extends BaseRequest
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
             * An array of permission IDs to be assigned.
             *
             * Must contain valid permission IDs.
             * @var array $permissions
             * @example [1, 2, 3]
             */
            'permissions' => 'required|array',

            /**
             * Each item must be a valid permission ID.
             *
             * Checks existence in the permissions table.
             * @var int $permissions.*
             * @example 1
             */
            'permissions.*' => 'exists:permissions,id',
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
            'permissions.required' => 'At least one permission must be selected.',
            'permissions.*.exists' => 'One or more selected permissions do not exist.',
        ];
    }
}
