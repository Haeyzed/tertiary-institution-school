<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
             * Unique identifier of the user.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The full name of the user.
             *
             * @var string $name
             * @example "Alice Johnson"
             */
            'name' => $this->name,

            /**
             * The email address of the user.
             *
             * @var string $email
             * @example "alice.johnson@example.com"
             */
            'email' => $this->email,

            /**
             * The phone number of the user.
             *
             * @var string|null $phone
             * @example "+1234567890"
             */
            'phone' => $this->phone,

            /**
             * The gender of the user.
             *
             * @var string|null $gender
             * @example "female"
             */
            'gender' => $this->gender,

            /**
             * The profile photo path or URL.
             *
             * @var string|null $photo
             * @example "/uploads/photos/user123.jpg"
             */
            'photo' => $this->photo,

            /**
             * The residential address of the user.
             *
             * @var string|null $address
             * @example "789 Main Street, City, State, ZIP"
             */
            'address' => $this->address,

            /**
             * Timestamp when the user account was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the user account was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
