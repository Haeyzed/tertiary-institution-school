<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentResource extends JsonResource
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
             * Unique identifier of the parent.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The occupation of the parent.
             *
             * @var string|null $occupation
             * @example "Software Engineer"
             */
            'occupation' => $this->occupation,

            /**
             * The relationship to the student.
             *
             * @var string|null $relationship
             * @example "Father"
             */
            'relationship' => $this->relationship,

            /**
             * The ID of the user account associated with this parent.
             *
             * @var int $user_id
             * @example 1
             */
            'user_id' => $this->user_id,

            /**
             * The user account associated with this parent.
             *
             * @var UserResource|null $user
             */
            'user' => new UserResource($this->whenLoaded('user')),

            /**
             * Total number of students under this parent's care.
             *
             * @var int|null $students_count
             * @example 2
             */
            'students_count' => $this->when($this->students_count, $this->students_count),

            /**
             * A list of students under this parent's care.
             *
             * @var array|null $students
             */
            'students' => StudentResource::collection($this->whenLoaded('students')),

            /**
             * Timestamp when the parent record was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the parent record was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
