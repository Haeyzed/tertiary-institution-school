<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends BaseRequest
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
             * The name of the permission.
             *
             * Must be unique.
             * @var string $name
             * @example "edit_user"
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($this->route('permission'))
            ],

            /**
             * The guard name associated with the permission.
             *
             * Optional guard name.
             * @var string|null $guard_name
             * @example "api"
             */
            'guard_name' => 'sometimes|string|max:255',

            /**
             * An array of role IDs to associate this permission with.
             *
             * Optional list of roles.
             * @var array|null $roles
             * @example [1, 3]
             */
            'roles' => 'sometimes|array',

            /**
             * Each item must be a valid role ID.
             *
             * Validates existence.
             * @var int $roles.*
             * @example 2
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
            'name.required' => 'The permission name is required.',
            'name.unique' => 'This permission name is already taken.',
            'roles.*.exists' => 'One or more selected roles do not exist.',
        ];
    }
}
