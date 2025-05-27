<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
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
             * The unique identifier of the department.
             *
             * This is the primary key.
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the department.
             *
             * Full name of the department.
             * @var string $name
             * @example "Department of Computer Science"
             */
            'name' => $this->name,

            /**
             * The code of the department.
             *
             * Short unique identifier.
             * @var string $code
             * @example "CSC"
             */
            'code' => $this->code,

            /**
             * The ID of the faculty the department belongs to.
             *
             * Foreign key from faculties table.
             * @var int $faculty_id
             * @example 2
             */
            'faculty_id' => $this->faculty_id,

            /**
             * The faculty information.
             *
             * Loaded only if the faculty relationship is available.
             * @var FacultyResource|null $faculty
             * @example FacultyResource::make(...)
             */
            'faculty' => new FacultyResource($this->whenLoaded('faculty')),

            /**
             * Total number of programs in the department.
             *
             * Returned only if available.
             * @var int|null $programs_count
             * @example 4
             */
            'programs_count' => $this->when($this->programs_count, $this->programs_count),

            /**
             * A list of programs in the department.
             *
             * Loaded only if the relationship is available.
             * @var AnonymousResourceCollection $programs
             * @example ProgramResource::collection(...)
             */
            'programs' => ProgramResource::collection($this->whenLoaded('programs')),

            /**
             * Total number of courses in the department.
             *
             * Returned only if available.
             * @var int|null $courses_count
             * @example 12
             */
            'courses_count' => $this->when($this->courses_count, $this->courses_count),

            /**
             * A list of courses in the department.
             *
             * Loaded only if the relationship is available.
             * @var AnonymousResourceCollection $courses
             * @example CourseResource::collection(...)
             */
            'courses' => CourseResource::collection($this->whenLoaded('courses')),

            /**
             * Total number of staff in the department.
             *
             * Returned only if available.
             * @var int|null $staff_count
             * @example 8
             */
            'staff_count' => $this->when($this->staff_count, $this->staff_count),

            /**
             * A list of staff members in the department.
             *
             * Loaded only if the relationship is available.
             * @var AnonymousResourceCollection $staff
             * @example StaffResource::collection(...)
             */
            'staff' => StaffResource::collection($this->whenLoaded('staff')),

            /**
             * The date the department was created.
             *
             * ISO 8601 format.
             * @var string $created_at
             * @example "2024-01-01T10:00:00.000000Z"
             */
            'created_at' => $this->created_at,

            /**
             * The date the department was last updated.
             *
             * ISO 8601 format.
             * @var string $updated_at
             * @example "2024-05-20T16:45:00.000000Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
