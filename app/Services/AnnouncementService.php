<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AnnouncementService
{
    /**
     * Get all announcements with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllAnnouncements(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Announcement::query();

        if (!empty($relations)) {
            $query->with($relations);
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
     * Delete an announcement.
     *
     * @param int $id
     * @return bool
     */
    public function deleteAnnouncement(int $id): bool
    {
        $announcement = Announcement::query()->find($id);

        if (!$announcement) {
            return false;
        }

        return $announcement->delete();
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
