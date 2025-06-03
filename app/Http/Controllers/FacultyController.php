<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacultyRequest;
use App\Http\Resources\FacultyResource;
use App\Services\FacultyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    /**
     * The faculty service instance.
     *
     * @var FacultyService
     */
    protected FacultyService $facultyService;

    /**
     * Create a new controller instance.
     *
     * @param FacultyService $facultyService
     * @return void
     */
    public function __construct(FacultyService $facultyService)
    {
        $this->facultyService = $facultyService;
    }

    /**
     * Display a listing of the faculties.
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

        $faculties = $this->facultyService->getAllFaculties($term, $perPage, $relations, $deleted);

        return response()->success(
            FacultyResource::collection($faculties),
            'Faculties retrieved successfully'
        );
    }

    /**
     * Store a newly created faculty in storage.
     *
     * @param FacultyRequest $request
     * @return JsonResponse
     */
    public function store(FacultyRequest $request): JsonResponse
    {
        $faculty = $this->facultyService->createFaculty($request->validated());

        return response()->success(
            new FacultyResource($faculty),
            'Faculty created successfully',
            201
        );
    }

    /**
     * Display the specified faculty.
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

        $faculty = $this->facultyService->getFacultyById($id, $relations);

        if (!$faculty) {
            return response()->error('Faculty not found', null, 404);
        }

        return response()->success(
            new FacultyResource($faculty),
            'Faculty retrieved successfully'
        );
    }

    /**
     * Update the specified faculty in storage.
     *
     * @param FacultyRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(FacultyRequest $request, int $id): JsonResponse
    {
        $faculty = $this->facultyService->updateFaculty($id, $request->validated());

        if (!$faculty) {
            return response()->error('Faculty not found', null, 404);
        }

        return response()->success(
            new FacultyResource($faculty),
            'Faculty updated successfully'
        );
    }

    /**
     * Remove the specified faculty from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $force = $request->boolean('force');

        $deleted = $this->facultyService->deleteFaculty($id, $force);

        if (!$deleted) {
            return response()->error('Faculty not found', null, 404);
        }

        return response()->success(
            null,
            'Faculty deleted successfully'
        );
    }

    /**
     * Restore a soft-deleted faculty.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $restored = $this->facultyService->restoreFaculty($id);

        if (!$restored) {
            return response()->error('Faculty not found or not deleted', null, 404);
        }

        return response()->success(
            new FacultyResource($restored),
            'Faculty restored successfully'
        );
    }
}
