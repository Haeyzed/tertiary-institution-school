<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{
    /**
     * Get all notifications with optional pagination.
     *
     * @param string $term
     * @param int|null $perPage
     * @param array $relations
     * @param bool|null $onlyDeleted
     * @return Collection|LengthAwarePaginator
     */
    public function getAllNotifications(string $term, ?int $perPage = null, array $relations = [], ?bool $onlyDeleted = null): Collection|LengthAwarePaginator
    {
        $query = Notification::query()
            ->where(function ($q) use ($term) {
                $q->whereLike('title', "%$term%")
                    ->orwhereLike('message', "%$term%");
            });

        if (!empty($relations)) {
            $query->with($relations);
        }

        if ($onlyDeleted === true) {
            $query->onlyTrashed();
        } elseif ($onlyDeleted === false) {
            $query->withoutTrashed();
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get a notification by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Notification|null
     */
    public function getNotificationById(int $id, array $relations = []): ?Notification
    {
        $query = Notification::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new notification.
     *
     * @param array $data
     * @return Notification
     */
    public function createNotification(array $data): Notification
    {
        return Notification::query()->create($data);
    }

    /**
     * Update an existing notification.
     *
     * @param int $id
     * @param array $data
     * @return Notification|null
     */
    public function updateNotification(int $id, array $data): ?Notification
    {
        $notification = Notification::query()->find($id);

        if (!$notification) {
            return null;
        }

        $notification->update($data);

        return $notification;
    }

    /**
     * Delete or force delete a notification.
     *
     * @param int $id
     * @param bool $force
     * @return bool
     */
    public function deleteNotification(int $id, bool $force = false): bool
    {
        $notification = Notification::withTrashed()->find($id);

        if (!$notification) {
            return false;
        }

        return $force ? $notification->forceDelete() : $notification->delete();
    }

    /**
     * Restore a delete notification.
     *
     * @param int $id
     * @return Notification|null
     */
    public function restoreNotification(int $id): ?Notification
    {
        $notification = Notification::onlyTrashed()->find($id);

        if (!$notification) {
            return null;
        }

        $notification->restore();

        return $notification->fresh();
    }

    /**
     * Get notifications by user.
     *
     * @param int $userId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getNotificationsByUser(int $userId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Notification::query()->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get unread notifications by user.
     *
     * @param int $userId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getUnreadNotificationsByUser(int $userId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Notification::query()->where('user_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc');

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Mark a notification as read.
     *
     * @param int $id
     * @return Notification|null
     */
    public function markAsRead(int $id): ?Notification
    {
        $notification = Notification::query()->find($id);

        if (!$notification) {
            return null;
        }

        $notification->update(['is_read' => true]);

        return $notification;
    }

    /**
     * Mark all notifications as read for a user.
     *
     * @param int $userId
     * @return bool
     */
    public function markAllAsRead(int $userId): bool
    {
        Notification::query()->where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return true;
    }
}
