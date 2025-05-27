<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcademicSessionRequest;
use App\Http\Resources\AcademicSessionResource;
use App\Services\AcademicSessionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;
use Throwable;

/**
 * @tags Academic Session
 */
class AcademicSessionController extends Controller
{
    /**
     * The academic session service instance.
     *
     * @var AcademicSessionService
     */
    protected AcademicSessionService $academicSessionService;

    /**
     * Create a new controller instance.
     *
     * @param AcademicSessionService $academicSessionService
     * @return void
     */
    public function __construct(AcademicSessionService $academicSessionService)
    {
        $this->academicSessionService = $academicSessionService;
    }

    /**
     * Display a listing of the academic sessions.
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

        $academicSessions = $this->academicSessionService->getAllAcademicSessions($perPage, $relations);

        return response()->success(
            AcademicSessionResource::collection($academicSessions),
            'Academic sessions retrieved successfully'
        );
    }

    /**
     * Store a newly created academic session in storage.
     *
     * @param AcademicSessionRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(AcademicSessionRequest $request): JsonResponse
    {
        try {
            $academicSession = $this->academicSessionService->createAcademicSession($request->validated());

            return response()->success(
                new AcademicSessionResource($academicSession),
                'Academic session created successfully',
                201
            );
        } catch (Exception $e) {
            return response()->error(
                'Failed to create academic session',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Display the specified academic session.
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

        $academicSession = $this->academicSessionService->getAcademicSessionById($id, $relations);

        if (!$academicSession) {
            return response()->error('Academic session not found', null, 404);
        }

        return response()->success(
            new AcademicSessionResource($academicSession),
            'Academic session retrieved successfully'
        );
    }

    /**
     * Update the specified academic session in storage.
     *
     * @param AcademicSessionRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(AcademicSessionRequest $request, int $id): JsonResponse
    {
        try {
            $academicSession = $this->academicSessionService->updateAcademicSession($id, $request->validated());

            if (!$academicSession) {
                return response()->error('Academic session not found', null, 404);
            }

            return response()->success(
                new AcademicSessionResource($academicSession),
                'Academic session updated successfully'
            );
        } catch (Exception $e) {
            return response()->error(
                'Failed to update academic session',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Remove the specified academic session from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->academicSessionService->deleteAcademicSession($id);

        if (!$deleted) {
            return response()->error('Academic session not found', null, 404);
        }

        return response()->success(
            null,
            'Academic session deleted successfully'
        );
    }

    /**
     * Get the current academic session.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCurrent(Request $request): JsonResponse
    {
        $relations = $request->query('with', []);

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $academicSession = $this->academicSessionService->getCurrentAcademicSession($relations);

        if (!$academicSession) {
            return response()->error('No current academic session found', null, 404);
        }

        return response()->success(
            new AcademicSessionResource($academicSession),
            'Current academic session retrieved successfully'
        );
    }

    /**
     * Set an academic session as current.
     *
     * @param int $id
     * @return JsonResponse
     * @throws Throwable
     */
    public function setCurrent(int $id): JsonResponse
    {
        try {
            $academicSession = $this->academicSessionService->setCurrentAcademicSession($id);

            if (!$academicSession) {
                return response()->error('Academic session not found', null, 404);
            }

            return response()->success(
                new AcademicSessionResource($academicSession),
                'Academic session set as current successfully'
            );
        } catch (Exception $e) {
            return response()->error(
                'Failed to set academic session as current',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
