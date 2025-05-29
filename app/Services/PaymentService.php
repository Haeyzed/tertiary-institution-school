<?php

namespace App\Services;

use App\Enums\PaymentStatusEnum;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentService
{
    /**
     * Get all payments with optional pagination.
     *
     * @param int|null $perPage
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function getAllPayments(?int $perPage = null, array $relations = []): Collection|LengthAwarePaginator
    {
        $query = Payment::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get a payment by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Payment|null
     */
    public function getPaymentById(int $id, array $relations = []): ?Payment
    {
        $query = Payment::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new payment.
     *
     * @param array $data
     * @return Payment
     */
    public function createPayment(array $data): Payment
    {
        $payment = Payment::query()->create($data);

        // Update payment status if needed
        $this->updatePaymentStatus($payment);

        return $payment;
    }

    /**
     * Update payment status based on amount paid vs fee amount.
     *
     * @param Payment $payment
     * @return void
     */
    private function updatePaymentStatus(Payment $payment): void
    {
        $fee = Fee::query()->find($payment->fee_id);

        if (!$fee) {
            return;
        }

        // Get total payments for this fee by this student
        $totalPaid = Payment::query()->where([
            'student_id' => $payment->student_id,
            'fee_id' => $payment->fee_id,
        ])->sum('amount_paid');

        if ($totalPaid >= $fee->amount) {
            $payment->update(['status' => PaymentStatusEnum::COMPLETED]);
        } elseif ($totalPaid > 0) {
            $payment->update(['status' => PaymentStatusEnum::PARTIAL]);
        } else {
            $payment->update(['status' => PaymentStatusEnum::PENDING]);
        }
    }

    /**
     * Update an existing payment.
     *
     * @param int $id
     * @param array $data
     * @return Payment|null
     */
    public function updatePayment(int $id, array $data): ?Payment
    {
        $payment = Payment::query()->find($id);

        if (!$payment) {
            return null;
        }

        $payment->update($data);

        // Update payment status if amount changed
        if (isset($data['amount_paid'])) {
            $this->updatePaymentStatus($payment);
        }

        return $payment;
    }

    /**
     * Delete a payment.
     *
     * @param int $id
     * @return bool
     */
    public function deletePayment(int $id): bool
    {
        $payment = Payment::query()->find($id);

        if (!$payment) {
            return false;
        }

        return $payment->delete();
    }

    /**
     * Get payments by student.
     *
     * @param int $studentId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getPaymentsByStudent(int $studentId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Payment::query()->where('student_id', $studentId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get payments by fee.
     *
     * @param int $feeId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getPaymentsByFee(int $feeId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Payment::query()->where('fee_id', $feeId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get student's fee balance.
     *
     * @param int $studentId
     * @param int|null $feeId
     * @return array
     */
    public function getStudentFeeBalance(int $studentId, ?int $feeId = null): array
    {
        $student = Student::query()->find($studentId);

        if (!$student) {
            return [
                'total_fees' => 0,
                'total_paid' => 0,
                'balance' => 0,
            ];
        }

        // Get all fees applicable to the student's program
        $feesQuery = Fee::query()->where('program_id', $student->program_id);

        if ($feeId) {
            $feesQuery->where('id', $feeId);
        }

        $fees = $feesQuery->get();

        $totalFees = $fees->sum('amount');

        // Get total payments made by the student
        $paymentsQuery = Payment::query()->where('student_id', $studentId);

        if ($feeId) {
            $paymentsQuery->where('fee_id', $feeId);
        }

        $totalPaid = $paymentsQuery->sum('amount_paid');

        return [
            'total_fees' => $totalFees,
            'total_paid' => $totalPaid,
            'balance' => $totalFees - $totalPaid,
        ];
    }
}
