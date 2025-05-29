<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
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
             * Unique identifier of the permission.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Name of the permission.
             *
             * @var string $name
             * @example "edit_users"
             */
            'name' => $this->name,

            /**
             * Guard name used for the permission.
             *
             * @var string $guard_name
             * @example "api"
             */
            'guard_name' => $this->guard_name,

            /**
             * Roles associated with the permission.
             *
             * @var array|null $roles
             */
            'roles' => RoleResource::collection($this->whenLoaded('roles')),

            /**
             * Timestamp when the permission was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the permission was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
