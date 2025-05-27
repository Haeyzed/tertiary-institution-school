<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
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
             * Unique identifier of the staff member.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The unique staff identification number.
             *
             * @var string $staff_id
             * @example "STAFF001"
             */
            'staff_id' => $this->staff_id,

            /**
             * The position or job title of the staff member.
             *
             * @var string|null $position
             * @example "Associate Professor"
             */
            'position' => $this->position,

            /**
             * The date when the staff member joined the institution.
             *
             * @var string|null $joining_date
             * @example "2020-09-01"
             */
            'joining_date' => $this->joining_date,

            /**
             * The ID of the user account associated with this staff member.
             *
             * @var int $user_id
             * @example 1
             */
            'user_id' => $this->user_id,

            /**
             * The ID of the department the staff member belongs to.
             *
             * @var int $department_id
             * @example 1
             */
            'department_id' => $this->department_id,

            /**
             * The user account associated with this staff member.
             *
             * @var UserResource|null $user
             */
            'user' => new UserResource($this->whenLoaded('user')),

            /**
             * The department the staff member belongs to.
             *
             * @var DepartmentResource|null $department
             */
            'department' => new DepartmentResource($this->whenLoaded('department')),

            /**
             * Total number of courses taught by this staff member.
             *
             * @var int|null $courses_count
             * @example 4
             */
            'courses_count' => $this->when($this->courses_count, $this->courses_count),

            /**
             * A list of courses taught by this staff member.
             *
             * @var array|null $courses
             */
            'courses' => CourseResource::collection($this->whenLoaded('courses')),

            /**
             * Total number of timetable entries for this staff member.
             *
             * @var int|null $timetables_count
             * @example 12
             */
            'timetables_count' => $this->when($this->timetables_count, $this->timetables_count),

            /**
             * A list of timetable entries for this staff member.
             *
             * @var array|null $timetables
             */
            'timetables' => TimetableResource::collection($this->whenLoaded('timetables')),

            /**
             * Total number of assignments created by this staff member.
             *
             * @var int|null $assignments_count
             * @example 15
             */
            'assignments_count' => $this->when($this->assignments_count, $this->assignments_count),

            /**
             * A list of assignments created by this staff member.
             *
             * @var array|null $assignments
             */
            'assignments' => AssignmentResource::collection($this->whenLoaded('assignments')),

            /**
             * Timestamp when the staff record was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the staff record was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
