<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class FacultyResource extends JsonResource
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
             * The unique identifier of the faculty.
             *
             * This is the primary key.
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the faculty.
             *
             * Full name of the faculty.
             * @var string $name
             * @example "Faculty of Engineering"
             */
            'name' => $this->name,

            /**
             * The short code for the faculty.
             *
             * Used for internal reference.
             * @var string $code
             * @example "ENG"
             */
            'code' => $this->code,

            /**
             * The number of departments in the faculty.
             *
             * This field is only shown if the count is available.
             * @var int|null $departments_count
             * @example 5
             */
            'departments_count' => $this->when($this->departments_count, $this->departments_count),

            /**
             * A list of departments under the faculty.
             *
             * Returned only if the relationship is loaded.
             * @var AnonymousResourceCollection $departments
             * @example DepartmentResource::collection(...)
             */
            'departments' => DepartmentResource::collection($this->whenLoaded('departments')),

            /**
             * The date the faculty was created.
             *
             * ISO 8601 formatted timestamp.
             * @var string $created_at
             * @example "2024-01-01T10:00:00.000000Z"
             */
            'created_at' => $this->created_at,

            /**
             * The date the faculty was last updated.
             *
             * ISO 8601 formatted timestamp.
             * @var string $updated_at
             * @example "2024-01-10T15:30:00.000000Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
