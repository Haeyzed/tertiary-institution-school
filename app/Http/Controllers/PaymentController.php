<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    /**
     * The payment service instance.
     *
     * @var PaymentService
     */
    protected PaymentService $paymentService;

    /**
     * Create a new controller instance.
     *
     * @param PaymentService $paymentService
     * @return void
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the payments.
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

        $payments = $this->paymentService->getAllPayments($perPage, $relations);

        return response()->success(
            PaymentResource::collection($payments),
            'Payments retrieved successfully'
        );
    }

    /**
     * Store a newly created payment in storage.
     *
     * @param PaymentRequest $request
     * @return JsonResponse
     */
    public function store(PaymentRequest $request): JsonResponse
    {
        $payment = $this->paymentService->createPayment($request->validated());

        return response()->success(
            new PaymentResource($payment),
            'Payment created successfully',
            201
        );
    }

    /**
     * Display the specified payment.
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

        $payment = $this->paymentService->getPaymentById($id, $relations);

        if (!$payment) {
            return response()->error('Payment not found', null, 404);
        }

        return response()->success(
            new PaymentResource($payment),
            'Payment retrieved successfully'
        );
    }

    /**
     * Update the specified payment in storage.
     *
     * @param PaymentRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(PaymentRequest $request, int $id): JsonResponse
    {
        $payment = $this->paymentService->updatePayment($id, $request->validated());

        if (!$payment) {
            return response()->error('Payment not found', null, 404);
        }

        return response()->success(
            new PaymentResource($payment),
            'Payment updated successfully'
        );
    }

    /**
     * Remove the specified payment from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->paymentService->deletePayment($id);

        if (!$deleted) {
            return response()->error('Payment not found', null, 404);
        }

        return response()->success(
            null,
            'Payment deleted successfully'
        );
    }

    /**
     * Get payments by student.
     *
     * @param Request $request
     * @param int $studentId
     * @return JsonResponse
     */
    public function getByStudent(Request $request, int $studentId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $payments = $this->paymentService->getPaymentsByStudent($studentId, $perPage);

        return response()->success(
            PaymentResource::collection($payments),
            'Payments retrieved successfully'
        );
    }

    /**
     * Get payments by fee.
     *
     * @param Request $request
     * @param int $feeId
     * @return JsonResponse
     */
    public function getByFee(Request $request, int $feeId): JsonResponse
    {
        $perPage = $request->query('per_page', config('app.per_page'));
        $payments = $this->paymentService->getPaymentsByFee($feeId, $perPage);

        return response()->success(
            PaymentResource::collection($payments),
            'Payments retrieved successfully'
        );
    }

    /**
     * Get student's fee balance.
     *
     * @param int $studentId
     * @param int|null $feeId
     * @return JsonResponse
     */
    public function getStudentFeeBalance(int $studentId, ?int $feeId = null): JsonResponse
    {
        $balance = $this->paymentService->getStudentFeeBalance($studentId, $feeId);

        return response()->success(
            $balance,
            'Student fee balance retrieved successfully'
        );
    }
}
