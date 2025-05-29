<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProgramRequest;
use App\Http\Resources\ProgramResource;
use App\Services\ProgramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * The program service instance.
     *
     * @var ProgramService
     */
    protected ProgramService $programService;

    /**
     * Create a new controller instance.
     *
     * @param ProgramService $programService
     * @return void
     */
    public function __construct(ProgramService $programService)
    {
        $this->programService = $programService;
    }

    /**
     * Display a listing of the programs.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $relations = $request->query('with', []);

        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $programs = $this->programService->getAllPrograms($perPage, $relations);

        return response()->success(
            ProgramResource::collection($programs),
            'Programs retrieved successfully'
        );
    }

    /**
     * Store a newly created program in storage.
     *
     * @param ProgramRequest $request
     * @return JsonResponse
     */
    public function store(ProgramRequest $request): JsonResponse
    {
        $program = $this->programService->createProgram($request->validated());

        return response()->success(
            new ProgramResource($program),
            'Program created successfully',
            201
        );
    }

    /**
     * Display the specified program.
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

        $program = $this->programService->getProgramById($id, $relations);

        if (!$program) {
            return response()->error('Program not found', null, 404);
        }

        return response()->success(
            new ProgramResource($program),
            'Program retrieved successfully'
        );
    }

    /**
     * Update the specified program in storage.
     *
     * @param ProgramRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(ProgramRequest $request, int $id): JsonResponse
    {
        $program = $this->programService->updateProgram($id, $request->validated());

        if (!$program) {
            return response()->error('Program not found', null, 404);
        }

        return response()->success(
            new ProgramResource($program),
            'Program updated successfully'
        );
    }

    /**
     * Remove the specified program from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->programService->deleteProgram($id);

        if (!$deleted) {
            return response()->error('Program not found', null, 404);
        }

        return response()->success(
            null,
            'Program deleted successfully'
        );
    }

    /**
     * Get programs by department.
     *
     * @param Request $request
     * @param int $departmentId
     * @return JsonResponse
     */
    public function getByDepartment(Request $request, int $departmentId): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $programs = $this->programService->getProgramsByDepartment($departmentId, $perPage);

        return response()->success(
            ProgramResource::collection($programs),
            'Programs retrieved successfully'
        );
    }

    /**
     * Search programs by name or code.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'term' => 'required|string|min:2',
        ]);

        $perPage = $request->query('per_page', 15);
        $programs = $this->programService->searchPrograms($request->term, $perPage);

        return response()->success(
            ProgramResource::collection($programs),
            'Search results retrieved successfully'
        );
    }
}
