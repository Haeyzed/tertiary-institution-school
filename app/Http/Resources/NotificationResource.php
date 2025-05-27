<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * Unique identifier of the notification.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The title of the notification.
             *
             * @var string $title
             * @example "Assignment Due Reminder"
             */
            'title' => $this->title,

            /**
             * The main content of the notification.
             *
             * @var string $message
             * @example "Your assignment for CS201 is due tomorrow at 11:59 PM."
             */
            'message' => $this->message,

            /**
             * The type/category of the notification.
             *
             * @var string|null $type
             * @example "assignment"
             */
            'type' => $this->type,

            /**
             * Whether the notification has been read.
             *
             * @var bool|null $is_read
             * @example false
             */
            'is_read' => $this->is_read,

            /**
             * The ID of the user who should receive the notification.
             *
             * @var int|null $user_id
             * @example 1
             */
            'user_id' => $this->user_id,

            /**
             * The user who should receive this notification.
             *
             * @var UserResource|null $user
             */
            'user' => new UserResource($this->whenLoaded('user')),

            /**
             * Timestamp when the notification was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the notification was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
