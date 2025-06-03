<?php

namespace App\Http\Resources;

use App\Models\Upload;
use App\Services\UploadService;
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
        $data = [
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
             * The user type.
             *
             * @var string|null $user_type
             * @example "student"
             */
            'user_type' => $this->user_type?->value,

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
             * The profile photo URLs (if photo exists).
             *
             * @var array|null $photo_urls
             */
            'photo_urls' => $this->when($this->photo, function () {
                $upload = Upload::query()->where('user_id', $this->id)
                    ->where('file_path', $this->photo)
                    ->first();

                if ($upload) {
                    return [
                        'original' => $upload->public_url,
                        'thumbnails' => $upload->getThumbnailUrls(),
                    ];
                }
                return null;
            }),

            /**
             * The residential address of the user.
             *
             * @var string|null $address
             * @example "789 Main Street, City, State, ZIP"
             */
            'address' => $this->address,

            /**
             * Whether the email has been verified.
             *
             * @var bool $email_verified
             * @example true
             */
            'email_verified' => !is_null($this->email_verified_at),

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

        // Include roles and permissions if they are loaded
        if ($this->relationLoaded('roles')) {
            $data['roles'] = $this->roles->pluck('name');
        }

        if ($this->relationLoaded('permissions')) {
            $data['direct_permissions'] = $this->permissions->pluck('name');
        }

        // Include all permissions (from roles and direct) if requested
        if ($request->has('include_all_permissions') && $request->include_all_permissions) {
            $data['all_permissions'] = $this->getAllPermissions()->pluck('name')->unique()->values();
        }

        return $data;
    }
}
