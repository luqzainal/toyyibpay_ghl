<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ToyyibPayService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected ToyyibPayService $toyyibPayService;

    public function __construct(ToyyibPayService $toyyibPayService)
    {
        $this->toyyibPayService = $toyyibPayService;
    }

    /**
     * Display payment page within GHL iframe.
     *
     * @param Request $request
     * @param string $billCode
     * @return View
     */
    public function showPaymentPage(Request $request, string $billCode): View
    {
        try {
            $transaction = Transaction::where('toyyibpay_billcode', $billCode)->first();

            if (!$transaction) {
                Log::channel('toyyibpay_transactions')->error('Transaction not found for payment page', [
                    'bill_code' => $billCode,
                ]);

                return view('payment.error', [
                    'error' => 'Transaction not found',
                    'bill_code' => $billCode,
                ]);
            }

            // Check if transaction is still valid for payment
            if ($transaction->isCompleted()) {
                return view('payment.success', [
                    'transaction' => $transaction,
                    'message' => 'Payment has already been completed successfully.',
                ]);
            }

            if ($transaction->isFailed()) {
                return view('payment.error', [
                    'error' => 'This transaction has failed or been cancelled',
                    'transaction' => $transaction,
                ]);
            }

            // Get payment URL from ToyyibPay
            $paymentUrl = $transaction->getPaymentUrl();

            if (!$paymentUrl) {
                Log::channel('toyyibpay_transactions')->error('Payment URL not available', [
                    'transaction_id' => $transaction->id,
                    'bill_code' => $billCode,
                ]);

                return view('payment.error', [
                    'error' => 'Payment URL not available',
                    'transaction' => $transaction,
                ]);
            }

            return view('payment.show', [
                'transaction' => $transaction,
                'payment_url' => $paymentUrl,
                'bill_code' => $billCode,
            ]);

        } catch (\Exception $e) {
            Log::channel('toyyibpay_transactions')->error('Error displaying payment page', [
                'bill_code' => $billCode,
                'error' => $e->getMessage(),
            ]);

            return view('payment.error', [
                'error' => 'An error occurred while loading the payment page',
                'bill_code' => $billCode,
            ]);
        }
    }

    /**
     * Handle payment return from ToyyibPay.
     *
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function handlePaymentReturn(Request $request): View|RedirectResponse
    {
        try {
            $billCode = $request->query('billcode');
            $status = $request->query('status_id');

            Log::channel('payment_webhooks')->info('Payment return received', [
                'bill_code' => $billCode,
                'status_id' => $status,
                'query_params' => $request->query(),
            ]);

            if (!$billCode) {
                return view('payment.error', [
                    'error' => 'Invalid payment return - missing bill code',
                ]);
            }

            $transaction = Transaction::where('toyyibpay_billcode', $billCode)->first();

            if (!$transaction) {
                return view('payment.error', [
                    'error' => 'Transaction not found',
                    'bill_code' => $billCode,
                ]);
            }

            // Update transaction status based on return status
            $newStatus = $this->mapToyyibPayStatus($status);
            
            if ($transaction->status !== $newStatus) {
                $transaction->update([
                    'status' => $newStatus,
                    'toyyibpay_response_data' => array_merge(
                        $transaction->toyyibpay_response_data ?? [],
                        ['return_data' => $request->query()]
                    ),
                ]);

                Log::channel('payment_webhooks')->info('Transaction status updated from return', [
                    'transaction_id' => $transaction->id,
                    'old_status' => $transaction->getOriginal('status'),
                    'new_status' => $newStatus,
                ]);
            }

            // Show appropriate result page
            if ($newStatus === Transaction::STATUS_COMPLETED) {
                return view('payment.success', [
                    'transaction' => $transaction,
                    'message' => 'Payment completed successfully!',
                ]);
            } elseif ($newStatus === Transaction::STATUS_FAILED) {
                return view('payment.failed', [
                    'transaction' => $transaction,
                    'message' => 'Payment failed or was cancelled.',
                ]);
            } else {
                return view('payment.pending', [
                    'transaction' => $transaction,
                    'message' => 'Payment is being processed. Please wait for confirmation.',
                ]);
            }

        } catch (\Exception $e) {
            Log::channel('payment_webhooks')->error('Error handling payment return', [
                'query_params' => $request->query(),
                'error' => $e->getMessage(),
            ]);

            return view('payment.error', [
                'error' => 'An error occurred while processing your payment return',
            ]);
        }
    }

    /**
     * Display payment status page.
     *
     * @param Request $request
     * @param string $billCode
     * @return View
     */
    public function showPaymentStatus(Request $request, string $billCode): View
    {
        try {
            $transaction = Transaction::where('toyyibpay_billcode', $billCode)->first();

            if (!$transaction) {
                return view('payment.error', [
                    'error' => 'Transaction not found',
                    'bill_code' => $billCode,
                ]);
            }

            return view('payment.status', [
                'transaction' => $transaction,
                'bill_code' => $billCode,
            ]);

        } catch (\Exception $e) {
            Log::channel('toyyibpay_transactions')->error('Error displaying payment status', [
                'bill_code' => $billCode,
                'error' => $e->getMessage(),
            ]);

            return view('payment.error', [
                'error' => 'An error occurred while loading the payment status',
                'bill_code' => $billCode,
            ]);
        }
    }

    /**
     * Refresh payment status from ToyyibPay API.
     *
     * @param Request $request
     * @param string $billCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshPaymentStatus(Request $request, string $billCode): \Illuminate\Http\JsonResponse
    {
        try {
            $transaction = Transaction::where('toyyibpay_billcode', $billCode)->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found',
                ], 404);
            }

            $integration = $transaction->integration;
            
            if (!$integration || !$integration->toyyibPayConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration not found',
                ], 404);
            }

            // Get latest status from ToyyibPay
            $statusData = $this->toyyibPayService->getBillStatus(
                $integration->toyyibPayConfig,
                $billCode
            );

            if ($statusData) {
                $toyyibPayStatus = $statusData[0]['billpaymentStatus'] ?? null;
                $newStatus = $this->mapToyyibPayStatus($toyyibPayStatus);

                if ($transaction->status !== $newStatus) {
                    $transaction->update([
                        'status' => $newStatus,
                        'toyyibpay_response_data' => array_merge(
                            $transaction->toyyibpay_response_data ?? [],
                            ['status_refresh' => $statusData]
                        ),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'updated_at' => $transaction->updated_at->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel('toyyibpay_transactions')->error('Error refreshing payment status', [
                'bill_code' => $billCode,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error refreshing payment status',
            ], 500);
        }
    }

    /**
     * Map ToyyibPay status to our transaction status.
     *
     * @param string|null $toyyibPayStatus
     * @return string
     */
    protected function mapToyyibPayStatus(?string $toyyibPayStatus): string
    {
        return match ($toyyibPayStatus) {
            '1' => Transaction::STATUS_COMPLETED, // Success
            '2' => Transaction::STATUS_PENDING,   // Pending
            '3' => Transaction::STATUS_FAILED,    // Failed
            default => Transaction::STATUS_FAILED,
        };
    }
}