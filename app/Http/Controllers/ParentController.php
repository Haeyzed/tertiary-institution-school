<?php

namespace App\Http\Controllers;

use App\Http\Requests\ParentRequest;
use App\Http\Resources\ParentResource;
use App\Services\ParentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ParentController extends Controller
{
    /**
     * The parent service instance.
     *
     * @var ParentService
     */
    protected ParentService $parentService;

    /**
     * Create a new controller instance.
     *
     * @param ParentService $parentService
     * @return void
     */
    public function __construct(ParentService $parentService)
    {
        $this->parentService = $parentService;
    }

    /**
     * Display a listing of the parents.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $relations = $request->query('with', []);
        $deleted = $request->boolean('deleted', null);
        $term = $request->query('term', '');

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $parents = $this->parentService->getAllParents($term, $perPage, $relations, $deleted);

        return response()->success(
            ParentResource::collection($parents),
            'Parents retrieved successfully'
        );
    }

    /**
     * Store a newly created parent in storage.
     *
     * @param ParentRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(ParentRequest $request): JsonResponse
    {
        try {
            $parent = $this->parentService->createParent($request->validated());

            return response()->success(
                new ParentResource($parent),
                'Parent created successfully',
                201
            );
        } catch (Exception $e) {
            return response()->error(
                'Failed to create parent',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Display the specified parent.
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

        $parent = $this->parentService->getParentById($id, $relations);

        if (!$parent) {
            return response()->error('Parent not found', null, 404);
        }

        return response()->success(
            new ParentResource($parent),
            'Parent retrieved successfully'
        );
    }

    /**
     * Update the specified parent in storage.
     *
     * @param ParentRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(ParentRequest $request, int $id): JsonResponse
    {
        try {
            $parent = $this->parentService->updateParent($id, $request->validated());

            if (!$parent) {
                return response()->error('Parent not found', null, 404);
            }

            return response()->success(
                new ParentResource($parent),
                'Parent updated successfully'
            );
        } catch (Exception $e) {
            return response()->error(
                'Failed to update parent',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Remove the specified parent from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws Throwable
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $force = $request->boolean('force');

        try {
            $deleted = $this->parentService->deleteParent($id, $force);

            if (!$deleted) {
                return response()->error('Parent not found', null, 404);
            }

            return response()->success(
                null,
                'Parent deleted successfully'
            );
        } catch (Exception $e) {
            return response()->error(
                'Failed to delete parent',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Restore a soft-deleted parent.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $restored = $this->parentService->restoreParent($id);

        if (!$restored) {
            return response()->error('Parent not found or not deleted', null, 404);
        }

        return response()->success(
            new ParentResource($restored),
            'Parent restored successfully'
        );
    }
}
