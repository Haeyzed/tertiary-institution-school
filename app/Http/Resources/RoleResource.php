<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
             * Unique identifier of the role.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Name of the role.
             *
             * @var string $name
             * @example "Administrator"
             */
            'name' => $this->name,

            /**
             * Guard name used for the role.
             *
             * @var string $guard_name
             * @example "api"
             */
            'guard_name' => $this->guard_name,

            /**
             * Permissions associated with the role.
             *
             * @var array|null $permissions
             */
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),

            /**
             * Timestamp when the role was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the role was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
