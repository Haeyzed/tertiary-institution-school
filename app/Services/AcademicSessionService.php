<?php

namespace App\Services;

use App\Models\AcademicSession;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

class AcademicSessionService
{
    /**
     * Get all academic sessions with optional pagination.
     *
     * @param string $term
     * @param int|null $perPage
     * @param array $relations
     * @param bool|null $onlyDeleted
     * @return Collection|LengthAwarePaginator
     */
    public function getAllAcademicSessions(string $term, ?int $perPage = null, array $relations = [], ?bool $onlyDeleted = null): Collection|LengthAwarePaginator
    {
        $query = AcademicSession::query()
            ->where(function ($q) use ($term) {
                $q->whereLike('name', "%$term%");
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
     * Get an academic session by ID.
     *
     * @param int $id
     * @param array $relations
     * @return AcademicSession|null
     */
    public function getAcademicSessionById(int $id, array $relations = []): ?AcademicSession
    {
        $query = AcademicSession::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new academic session.
     *
     * @param array $data
     * @return AcademicSession
     * @throws Throwable
     */
    public function createAcademicSession(array $data): AcademicSession
    {
        return DB::transaction(function () use ($data) {
            // If this session is set as current, unset all others
            if (!empty($data['is_current'])) {
                AcademicSession::query()->where('is_current', true)->update(['is_current' => false]);
            }

            return AcademicSession::query()->create($data);
        });
    }


    /**
     * Update an existing academic session.
     *
     * @param int $id
     * @param array $data
     * @return AcademicSession|null
     * @throws Throwable
     */
    public function updateAcademicSession(int $id, array $data): ?AcademicSession
    {
        $academicSession = AcademicSession::query()->find($id);

        if (!$academicSession) {
            return null;
        }

        return DB::transaction(function () use ($academicSession, $data, $id) {
            // If this session is set as current, unset all others
            if (!empty($data['is_current'])) {
                AcademicSession::query()
                    ->where('id', '!=', $id)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
            }

            $academicSession->update($data);

            return $academicSession;
        });
    }

    /**
     * Delete or force delete an academic session.
     *
     * @param int $id
     * @param bool $force
     * @return bool
     */
    public function deleteAcademicSession(int $id, bool $force = false): bool
    {
        $academicSession = AcademicSession::withTrashed()->find($id);

        if (!$academicSession) {
            return false;
        }

        if ($academicSession->is_current) {
            return false;
        }

        return $force ? $academicSession->forceDelete() : $academicSession->delete();
    }

    /**
     * Restore a delete academic session.
     *
     * @param int $id
     * @return AcademicSession|null
     */
    public function restoreAcademicSession(int $id): ?AcademicSession
    {
        $academicSession = AcademicSession::onlyTrashed()->find($id);

        if (!$academicSession) {
            return null;
        }

        $academicSession->restore();

        return $academicSession->fresh();
    }

    /**
     * Get the current academic session.
     *
     * @param array $relations
     * @return AcademicSession|null
     */
    public function getCurrentAcademicSession(array $relations = []): ?AcademicSession
    {
        $query = AcademicSession::query()->where('is_current', true);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }

    /**
     * Set an academic session as current.
     *
     * @param int $id
     * @return AcademicSession|null
     * @throws Throwable
     */
    public function setCurrentAcademicSession(int $id): ?AcademicSession
    {
        $academicSession = AcademicSession::query()->find($id);

        if (!$academicSession) {
            return null;
        }

        return DB::transaction(function () use ($academicSession) {
            // Unset all current sessions
            AcademicSession::query()->where('is_current', true)->update(['is_current' => false]);

            // Set this session as current
            $academicSession->update(['is_current' => true]);

            return $academicSession;
        });
    }
}
