<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * The notification service instance.
     *
     * @var NotificationService
     */
    protected NotificationService $notificationService;

    /**
     * Create a new controller instance.
     *
     * @param NotificationService $notificationService
     * @return void
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the notifications.
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

        $notifications = $this->notificationService->getAllNotifications($perPage, $relations);

        return response()->success(
            NotificationResource::collection($notifications),
            'Notifications retrieved successfully'
        );
    }

    /**
     * Store a newly created notification in storage.
     *
     * @param NotificationRequest $request
     * @return JsonResponse
     */
    public function store(NotificationRequest $request): JsonResponse
    {
        $notification = $this->notificationService->createNotification($request->validated());

        return response()->success(
            new NotificationResource($notification),
            'Notification created successfully',
            201
        );
    }

    /**
     * Display the specified notification.
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

        $notification = $this->notificationService->getNotificationById($id, $relations);

        if (!$notification) {
            return response()->error('Notification not found', null, 404);
        }

        return response()->success(
            new NotificationResource($notification),
            'Notification retrieved successfully'
        );
    }

    /**
     * Update the specified notification in storage.
     *
     * @param NotificationRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(NotificationRequest $request, int $id): JsonResponse
    {
        $notification = $this->notificationService->updateNotification($id, $request->validated());

        if (!$notification) {
            return response()->error('Notification not found', null, 404);
        }

        return response()->success(
            new NotificationResource($notification),
            'Notification updated successfully'
        );
    }

    /**
     * Remove the specified notification from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->notificationService->deleteNotification($id);

        if (!$deleted) {
            return response()->error('Notification not found', null, 404);
        }

        return response()->success(
            null,
            'Notification deleted successfully'
        );
    }

    /**
     * Get notifications by user.
     *
     * @param Request $request
     * @param int $userId
     * @return JsonResponse
     */
    public function getByUser(Request $request, int $userId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $notifications = $this->notificationService->getNotificationsByUser($userId, $perPage);

        return response()->success(
            NotificationResource::collection($notifications),
            'Notifications retrieved successfully'
        );
    }

    /**
     * Get unread notifications by user.
     *
     * @param Request $request
     * @param int $userId
     * @return JsonResponse
     */
    public function getUnreadByUser(Request $request, int $userId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $notifications = $this->notificationService->getUnreadNotificationsByUser($userId, $perPage);

        return response()->success(
            NotificationResource::collection($notifications),
            'Unread notifications retrieved successfully'
        );
    }

    /**
     * Mark a notification as read.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function markAsRead(int $id): JsonResponse
    {
        $notification = $this->notificationService->markAsRead($id);

        if (!$notification) {
            return response()->error('Notification not found', null, 404);
        }

        return response()->success(
            new NotificationResource($notification),
            'Notification marked as read successfully'
        );
    }

    /**
     * Mark all notifications as read for a user.
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function markAllAsRead(int $userId): JsonResponse
    {
        $this->notificationService->markAllAsRead($userId);

        return response()->success(
            null,
            'All notifications marked as read successfully'
        );
    }
}
