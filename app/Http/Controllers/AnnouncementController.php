<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Services\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * The announcement service instance.
     *
     * @var AnnouncementService
     */
    protected AnnouncementService $announcementService;

    /**
     * Create a new controller instance.
     *
     * @param AnnouncementService $announcementService
     * @return void
     */
    public function __construct(AnnouncementService $announcementService)
    {
        $this->announcementService = $announcementService;
    }

    /**
     * Display a listing of the announcements.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $relations = $request->query('with', []);

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $announcements = $this->announcementService->getAllAnnouncements($perPage, $relations);

        return response()->success(
            AnnouncementResource::collection($announcements),
            'Announcements retrieved successfully'
        );
    }

    /**
     * Store a newly created announcement in storage.
     *
     * @param AnnouncementRequest $request
     * @return JsonResponse
     */
    public function store(AnnouncementRequest $request): JsonResponse
    {
        $announcement = $this->announcementService->createAnnouncement($request->validated());

        return response()->success(
            new AnnouncementResource($announcement),
            'Announcement created successfully',
            201
        );
    }

    /**
     * Display the specified announcement.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $relations = $request->query('with', []);

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $announcement = $this->announcementService->getAnnouncementById($id, $relations);

        if (!$announcement) {
            return response()->error('Announcement not found', null, 404);
        }

        return response()->success(
            new AnnouncementResource($announcement),
            'Announcement retrieved successfully'
        );
    }

    /**
     * Update the specified announcement in storage.
     *
     * @param AnnouncementRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(AnnouncementRequest $request, int $id): JsonResponse
    {
        $announcement = $this->announcementService->updateAnnouncement($id, $request->validated());

        if (!$announcement) {
            return response()->error('Announcement not found', null, 404);
        }

        return response()->success(
            new AnnouncementResource($announcement),
            'Announcement updated successfully'
        );
    }

    /**
     * Remove the specified announcement from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->announcementService->deleteAnnouncement($id);

        if (!$deleted) {
            return response()->error('Announcement not found', null, 404);
        }

        return response()->success(
            null,
            'Announcement deleted successfully'
        );
    }

    /**
     * Get announcements by creator.
     *
     * @param Request $request
     * @param int $createdBy
     * @return JsonResponse
     */
    public function getByCreator(Request $request, int $createdBy): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $announcements = $this->announcementService->getAnnouncementsByCreator($createdBy, $perPage);

        return response()->success(
            AnnouncementResource::collection($announcements),
            'Announcements retrieved successfully'
        );
    }

    /**
     * Get recent announcements.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRecent(Request $request): JsonResponse
    {
        $limit = $request->query('limit', config('app.limit'));
        $announcements = $this->announcementService->getRecentAnnouncements($limit);

        return response()->success(
            AnnouncementResource::collection($announcements),
            'Recent announcements retrieved successfully'
        );
    }
}
