<?php

namespace App\Http\Requests;

use App\Enums\NotificationTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Request validation for notification operations.
 *
 * Handles validation for creating and updating notifications.
 */
class NotificationRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
             * The title of the notification.
             *
             * A brief, descriptive title for the notification.
             * @var string $title
             * @example "Assignment Due Reminder"
             */
            'title' => ['required', 'string', 'max:255'],

            /**
             * The main content of the notification.
             *
             * Detailed message content for the notification.
             * @var string $message
             * @example "Your assignment for CS201 is due tomorrow at 11:59 PM."
             */
            'message' => ['required', 'string'],

            /**
             * The ID of the user who should receive the notification.
             *
             * Optional - if null, notification may be for all users.
             * @var int|null $user_id
             * @example 1
             */
            'user_id' => ['nullable', 'integer', 'exists:users,id'],

            /**
             * The type/category of the notification.
             *
             * Must be one of the predefined notification types.
             * @var string|null $type
             * @example "assignment"
             */
            'type' => ['nullable', 'string', Rule::in(NotificationTypeEnum::values())],

            /**
             * Whether the notification has been read.
             *
             * Boolean flag indicating read status.
             * @var bool|null $is_read
             * @example false
             */
            'is_read' => ['nullable', 'boolean'],
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
            'user_id.exists' => 'The selected user does not exist.',
            'type.in' => 'Invalid notification type.',
        ];
    }
}
