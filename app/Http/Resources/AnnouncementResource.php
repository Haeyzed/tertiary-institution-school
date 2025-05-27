<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
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
             * Unique identifier of the announcement.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The title of the announcement.
             *
             * @var string $title
             * @example "Important Notice: Exam Schedule Update"
             */
            'title' => $this->title,

            /**
             * The main content/message of the announcement.
             *
             * @var string $message
             * @example "The final exam schedule has been updated. Please check your student portal for details."
             */
            'message' => $this->message,

            /**
             * The date when the announcement was made.
             *
             * @var string $date
             * @example "2024-12-01"
             */
            'date' => $this->date,

            /**
             * The ID of the user who created the announcement.
             *
             * @var int $created_by
             * @example 1
             */
            'created_by' => $this->created_by,

            /**
             * The user who created this announcement.
             *
             * @var UserResource|null $creator
             */
            'creator' => new UserResource($this->whenLoaded('creator')),

            /**
             * Timestamp when the announcement was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the announcement was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
