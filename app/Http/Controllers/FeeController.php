<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeeRequest;
use App\Http\Resources\FeeResource;
use App\Services\FeeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FeeController extends Controller
{
    /**
     * The fee service instance.
     *
     * @var FeeService
     */
    protected FeeService $feeService;

    /**
     * Create a new controller instance.
     *
     * @param FeeService $feeService
     * @return void
     */
    public function __construct(FeeService $feeService)
    {
        $this->feeService = $feeService;
    }

    /**
     * Display a listing of the fees.
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

        $fees = $this->feeService->getAllFees($perPage, $relations);

        return response()->success(
            FeeResource::collection($fees),
            'Fees retrieved successfully'
        );
    }

    /**
     * Store a newly created fee in storage.
     *
     * @param FeeRequest $request
     * @return JsonResponse
     */
    public function store(FeeRequest $request): JsonResponse
    {
        $fee = $this->feeService->createFee($request->validated());

        return response()->success(
            new FeeResource($fee),
            'Fee created successfully',
            201
        );
    }

    /**
     * Display the specified fee.
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

        $fee = $this->feeService->getFeeById($id, $relations);

        if (!$fee) {
            return response()->error('Fee not found', null, 404);
        }

        return response()->success(
            new FeeResource($fee),
            'Fee retrieved successfully'
        );
    }

    /**
     * Update the specified fee in storage.
     *
     * @param FeeRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(FeeRequest $request, int $id): JsonResponse
    {
        $fee = $this->feeService->updateFee($id, $request->validated());

        if (!$fee) {
            return response()->error('Fee not found', null, 404);
        }

        return response()->success(
            new FeeResource($fee),
            'Fee updated successfully'
        );
    }

    /**
     * Remove the specified fee from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->feeService->deleteFee($id);

        if (!$deleted) {
            return response()->error('Fee not found', null, 404);
        }

        return response()->success(
            null,
            'Fee deleted successfully'
        );
    }

    /**
     * Get fees by program.
     *
     * @param Request $request
     * @param int $programId
     * @return JsonResponse
     */
    public function getByProgram(Request $request, int $programId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $fees = $this->feeService->getFeesByProgram($programId, $perPage);

        return response()->success(
            FeeResource::collection($fees),
            'Fees retrieved successfully'
        );
    }

    /**
     * Get fees by semester.
     *
     * @param Request $request
     * @param int $semesterId
     * @return JsonResponse
     */
    public function getBySemester(Request $request, int $semesterId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $fees = $this->feeService->getFeesBySemester($semesterId, $perPage);

        return response()->success(
            FeeResource::collection($fees),
            'Fees retrieved successfully'
        );
    }

    /**
     * Get current semester fees.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCurrentSemesterFees(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $fees = $this->feeService->getCurrentSemesterFees($perPage);

        return response()->success(
            FeeResource::collection($fees),
            'Current semester fees retrieved successfully'
        );
    }
}
