<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AnnouncementService
{
    /**
     * Get all announcements with optional pagination.
     *
     * @param string $term
     * @param int|null $perPage
     * @param array $relations
     * @param bool|null $onlyDeleted
     * @return Collection|LengthAwarePaginator
     */
    public function getAllAnnouncements(string $term, ?int $perPage = null, array $relations = [], ?bool $onlyDeleted = null): Collection|LengthAwarePaginator
    {
        $query = Announcement::query()
            ->where(function ($q) use ($term) {
                $q->whereLike('title', "%$term%")
                    ->orwhereLike('message', "%$term%");
            });;

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
     * Get an announcement by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Announcement|null
     */
    public function getAnnouncementById(int $id, array $relations = []): ?Announcement
    {
        $query = Announcement::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new announcement.
     *
     * @param array $data
     * @return Announcement
     */
    public function createAnnouncement(array $data): Announcement
    {
        return Announcement::query()->create($data);
    }

    /**
     * Update an existing announcement.
     *
     * @param int $id
     * @param array $data
     * @return Announcement|null
     */
    public function updateAnnouncement(int $id, array $data): ?Announcement
    {
        $announcement = Announcement::query()->find($id);

        if (!$announcement) {
            return null;
        }

        $announcement->update($data);

        return $announcement;
    }

    /**
     * Delete or force delete an announcement.
     *
     * @param int $id
     * @param bool $force
     * @return bool
     */
    public function deleteAnnouncement(int $id, bool $force = false): bool
    {
        $announcement = Announcement::withTrashed()->find($id);

        if (!$announcement) {
            return false;
        }

        return $force ? $announcement->forceDelete() : $announcement->delete();
    }

    /**
     * Restore a delete announcement.
     *
     * @param int $id
     * @return Announcement|null
     */
    public function restoreAnnouncement(int $id): ?Announcement
    {
        $announcement = Announcement::onlyTrashed()->find($id);

        if (!$announcement) {
            return null;
        }

        $announcement->restore();

        return $announcement->fresh();
    }

    /**
     * Get announcements by creator.
     *
     * @param int $createdBy
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getAnnouncementsByCreator(int $createdBy, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Announcement::query()->where('created_by', $createdBy)
            ->orderBy('date', 'desc');

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get recent announcements.
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecentAnnouncements(int $limit = 5): Collection
    {
        return Announcement::query()->orderBy('date', 'desc')
            ->limit($limit)
            ->get();
    }
}
