<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class RoleRequest extends BaseRequest
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
             * The name of the role.
             *
             * Must be unique.
             * @var string $name
             * @example "Administrator"
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->route('role'))
            ],

            /**
             * The guard name associated with the role.
             *
             * Optional guard name.
             * @var string|null $guard_name
             * @example "web"
             */
            'guard_name' => 'sometimes|string|max:255',

            /**
             * An array of permission IDs assigned to this role.
             *
             * Optional list of permissions.
             * @var array|null $permissions
             * @example [1, 2]
             */
            'permissions' => 'sometimes|array',

            /**
             * Each item must be a valid permission ID.
             *
             * Validates existence.
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
            'name.required' => 'The role name is required.',
            'name.unique' => 'This role name is already taken.',
            'permissions.*.exists' => 'One or more selected permissions do not exist.',
        ];
    }
}
