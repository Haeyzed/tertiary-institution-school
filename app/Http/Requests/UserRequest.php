<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use App\Enums\UserTypeEnum;
use Illuminate\Validation\Rule;

/**
 * Request validation for user operations.
 *
 * Handles validation for creating and updating user accounts.
 */
class UserRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            /**
             * The full name of the user.
             *
             * A required full name for the user account.
             * @var string $name
             * @example "Alice Johnson"
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * The email address of the user.
             *
             * Must be unique and a valid email format.
             * @var string $email
             * @example "alice.johnson@example.com"
             */
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) use ($userId) {
                    $query = \App\Models\User::where('email', $value);

                    if ($this->has('user_type')) {
                        $query->where('user_type', $this->input('user_type'));
                    } else {
                        $user = \App\Models\User::find($userId);
                        if ($user) {
                            $query->where('user_type', $user->user_type);
                        }
                    }

                    if ($userId) {
                        $query->where('id', '!=', $userId);
                    }

                    if ($query->exists()) {
                        $fail('The email has already been taken for this user type.');
                    }
                }
            ],

            /**
             * The phone number of the user.
             *
             * Optional contact phone number.
             * @var string|null $phone
             * @example "+1234567890"
             */
            'phone' => ['nullable', 'string', 'max:20'],

            /**
             * The gender of the user.
             *
             * Must be one of the predefined gender options.
             * @var string|null $gender
             * @example "female"
             */
            'gender' => ['nullable', 'string', Rule::in(GenderEnum::values())],

            /**
             * The residential address of the user.
             *
             * Optional full address information.
             * @var string|null $address
             * @example "789 Main Street, City, State, ZIP"
             */
            'address' => ['nullable', 'string'],

            /**
             * The profile photo path or URL.
             *
             * Optional reference to user's profile picture.
             * @var string|null $photo
             * @example "/uploads/photos/user123.jpg"
             */
            'photo' => ['nullable', 'string'],

            /**
             * The password for the user account.
             *
             * Optional password, must be at least 8 characters if provided.
             * @var string|null $password
             * @example "securepassword123"
             */
            'password' => ['nullable', 'string', 'min:8'],

            /**
             * The user type.
             *
             * @var string|null $user_type
             * @example "student"
             */
            'user_type' => ['nullable', 'string', Rule::in(UserTypeEnum::values())],

            /**
             * The roles to assign to the user.
             *
             * @var array|null $roles
             * @example ["admin", "staff"]
             */
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],

            /**
             * The permissions to assign to the user.
             *
             * @var array|null $permissions
             * @example ["create-user", "edit-user"]
             */
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],

            /**
             * Relations to load with the user.
             *
             * @var array|null $with
             * @example ["roles", "permissions"]
             */
            'with' => ['nullable', 'array'],
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
            'email.unique' => 'This email address is already in use.',
            'password.min' => 'Password must be at least 8 characters long.',
            'gender.in' => 'Gender must be male, female, or other.',
            'user_type.in' => 'Invalid user type provided.',
            'roles.*.exists' => 'One or more selected roles do not exist.',
            'permissions.*.exists' => 'One or more selected permissions do not exist.',
        ];
    }
}
