<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ToyyibPayService;
use App\Services\GHLService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected ToyyibPayService $toyyibPayService;
    protected GHLService $ghlService;

    public function __construct(ToyyibPayService $toyyibPayService, GHLService $ghlService)
    {
        $this->toyyibPayService = $toyyibPayService;
        $this->ghlService = $ghlService;
    }

    /**
     * Handle ToyyibPay payment status callback.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleToyyibPayCallback(Request $request): JsonResponse
    {
        try {
            $callbackData = $request->all();

            Log::channel('payment_webhooks')->info('ToyyibPay callback received', [
                'callback_data' => $callbackData,
                'headers' => $request->headers->all(),
            ]);

            // Basic validation
            if (!isset($callbackData['billcode'])) {
                Log::channel('payment_webhooks')->error('ToyyibPay callback missing billcode', [
                    'callback_data' => $callbackData,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Missing billcode in callback',
                ], 400);
            }

            // Process the callback
            $success = $this->toyyibPayService->handleCallback($callbackData);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process callback',
                ], 500);
            }

            // Get the updated transaction
            $transaction = Transaction::where('toyyibpay_billcode', $callbackData['billcode'])->first();

            if ($transaction) {
                // Notify GHL of the payment status update
                $this->notifyGHLOfPaymentStatus($transaction);
            }

            return response()->json([
                'success' => true,
                'message' => 'Callback processed successfully',
            ]);

        } catch (\Exception $e) {
            Log::channel('payment_webhooks')->error('ToyyibPay callback processing failed', [
                'callback_data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error processing callback',
            ], 500);
        }
    }

    /**
     * Handle manual webhook test (for development/testing).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleTestWebhook(Request $request): JsonResponse
    {
        try {
            Log::channel('payment_webhooks')->info('Test webhook received', [
                'test_data' => $request->all(),
            ]);

            // Simulate ToyyibPay callback for testing
            $testData = [
                'billcode' => $request->input('billcode', 'test_billcode'),
                'status_id' => $request->input('status_id', '1'), // Default to success
                'order_id' => $request->input('order_id', 'test_order'),
                'msg' => $request->input('msg', 'Test webhook'),
                'transaction_id' => $request->input('transaction_id', 'test_txn'),
            ];

            $success = $this->toyyibPayService->handleCallback($testData);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Test webhook processed successfully' : 'Test webhook processing failed',
                'test_data' => $testData,
            ]);

        } catch (\Exception $e) {
            Log::channel('payment_webhooks')->error('Test webhook processing failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test webhook processing failed',
            ], 500);
        }
    }

    /**
     * Get webhook logs for debugging.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getWebhookLogs(Request $request): JsonResponse
    {
        try {
            $billCode = $request->query('bill_code');
            $limit = min($request->query('limit', 50), 100); // Max 100 logs

            $query = Transaction::query();

            if ($billCode) {
                $query->where('toyyibpay_billcode', $billCode);
            }

            $transactions = $query->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get();

            $logs = $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'bill_code' => $transaction->toyyibpay_billcode,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'callback_at' => $transaction->toyyibpay_callback_at,
                    'ghl_notified_at' => $transaction->ghl_notified_at,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                    'response_data' => $transaction->toyyibpay_response_data,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $logs,
                'total' => $logs->count(),
            ]);

        } catch (\Exception $e) {
            Log::channel('payment_webhooks')->error('Error fetching webhook logs', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching webhook logs',
            ], 500);
        }
    }

    /**
     * Notify GHL of payment status update.
     *
     * @param Transaction $transaction
     * @return void
     */
    protected function notifyGHLOfPaymentStatus(Transaction $transaction): void
    {
        try {
            $integration = $transaction->integration;

            if (!$integration) {
                Log::channel('payment_webhooks')->error('Integration not found for GHL notification', [
                    'transaction_id' => $transaction->id,
                ]);
                return;
            }

            // Prepare payment data for GHL webhook
            $paymentData = [
                'ghl_transaction_id' => $transaction->ghl_transaction_id,
                'toyyibpay_billcode' => $transaction->toyyibpay_billcode,
                'charge_id' => $transaction->toyyibpay_billcode,
                'status' => $this->mapTransactionStatusForGHL($transaction->status),
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'provider_transaction_id' => $transaction->toyyibpay_bill_id,
                'metadata' => [
                    'bill_code' => $transaction->toyyibpay_billcode,
                    'environment' => $transaction->environment,
                    'callback_received_at' => $transaction->toyyibpay_callback_at?->toISOString(),
                ],
            ];

            $success = $this->ghlService->sendPaymentStatusWebhook($integration, $paymentData);

            if ($success) {
                $transaction->markGHLNotified();
                
                Log::channel('payment_webhooks')->info('GHL notified of payment status', [
                    'transaction_id' => $transaction->id,
                    'status' => $transaction->status,
                ]);
            } else {
                Log::channel('payment_webhooks')->error('Failed to notify GHL of payment status', [
                    'transaction_id' => $transaction->id,
                    'status' => $transaction->status,
                ]);
            }

        } catch (\Exception $e) {
            Log::channel('payment_webhooks')->error('Error notifying GHL of payment status', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Map our transaction status to GHL-compatible status.
     *
     * @param string $status
     * @return string
     */
    protected function mapTransactionStatusForGHL(string $status): string
    {
        return match ($status) {
            Transaction::STATUS_COMPLETED => 'completed',
            Transaction::STATUS_FAILED, Transaction::STATUS_CANCELLED => 'failed',
            Transaction::STATUS_PENDING, Transaction::STATUS_PROCESSING => 'pending',
            default => 'failed',
        };
    }

    /**
     * Health check endpoint for webhook service.
     *
     * @return JsonResponse
     */
    public function healthCheck(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Webhook service is healthy',
            'timestamp' => now()->toISOString(),
            'services' => [
                'toyyibpay' => 'active',
                'ghl' => 'active',
                'database' => 'active',
                'logging' => 'active',
            ],
        ]);
    }
}