<?php

namespace App\Http\Requests;

/**
 * Request validation for announcement operations.
 *
 * Handles validation for creating and updating announcements.
 */
class AnnouncementRequest extends BaseRequest
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
             * The title of the announcement.
             *
             * A descriptive title for the announcement.
             * @var string $title
             * @example "Important Notice: Exam Schedule Update"
             */
            'title' => ['required', 'string', 'max:255'],

            /**
             * The main content/message of the announcement.
             *
             * Detailed information about the announcement.
             * @var string $message
             * @example "The final exam schedule has been updated. Please check your student portal for details."
             */
            'message' => ['required', 'string'],

            /**
             * The ID of the user who created the announcement.
             *
             * Must reference an existing user in the system.
             * @var int $created_by
             * @example 1
             */
            'created_by' => ['required', 'integer', 'exists:users,id'],

            /**
             * The date when the announcement was made.
             *
             * Must be a valid date format.
             * @var string $date
             * @example "2024-12-01"
             */
            'date' => ['required', 'date'],
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
            'created_by.exists' => 'The selected user does not exist.',
        ];
    }
}
