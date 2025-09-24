<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\Transaction;
use App\Services\ToyyibPayService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ToyyibPayController extends Controller
{
    protected ToyyibPayService $toyyibPayService;

    public function __construct(ToyyibPayService $toyyibPayService)
    {
        $this->toyyibPayService = $toyyibPayService;
    }

    /**
     * Create payment for GHL integration.
     * This endpoint is called by GHL when processing payments.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'locationId' => 'required|string',
            'transactionId' => 'required|string',
            'orderId' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'sometimes|string|in:MYR',
            'description' => 'sometimes|string|max:255',
            'customerName' => 'sometimes|string|max:255',
            'customerEmail' => 'sometimes|email|max:255',
            'customerPhone' => 'sometimes|string|max:20',
            'returnUrl' => 'sometimes|url',
        ]);

        if ($validator->fails()) {
            Log::channel('toyyibpay_transactions')->error('Payment creation validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid payment parameters',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $paymentData = [
                'transaction_id' => $request->input('transactionId'),
                'order_id' => $request->input('orderId'),
                'amount' => $request->input('amount'),
                'currency' => $request->input('currency', 'MYR'),
                'description' => $request->input('description', 'Payment via GHL'),
                'customer_name' => $request->input('customerName'),
                'customer_email' => $request->input('customerEmail'),
                'customer_phone' => $request->input('customerPhone'),
                'return_url' => $request->input('returnUrl'),
            ];

            $result = $this->toyyibPayService->createPaymentForGHL(
                $request->input('locationId'),
                $paymentData
            );

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment created successfully',
                'data' => [
                    'transactionId' => $result['transaction_id'],
                    'billCode' => $result['bill_code'],
                    'paymentUrl' => $result['payment_url'],
                    'status' => $result['status'],
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel('toyyibpay_transactions')->error('Payment creation failed', [
                'location_id' => $request->input('locationId'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error during payment creation',
            ], 500);
        }
    }

    /**
     * Get payment status by bill code.
     *
     * @param Request $request
     * @param string $billCode
     * @return JsonResponse
     */
    public function getPaymentStatus(Request $request, string $billCode): JsonResponse
    {
        try {
            $transaction = Transaction::where('toyyibpay_billcode', $billCode)->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found',
                ], 404);
            }

            // Get integration to access ToyyibPay config
            $integration = $transaction->integration;

            if (!$integration || !$integration->toyyibPayConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'ToyyibPay configuration not found',
                ], 404);
            }

            // Get latest status from ToyyibPay
            $statusData = $this->toyyibPayService->getBillStatus(
                $integration->toyyibPayConfig,
                $billCode
            );

            if ($statusData) {
                // Update transaction status if needed
                $toyyibPayStatus = $statusData[0]['billpaymentStatus'] ?? null;
                $newStatus = $this->mapToyyibPayStatus($toyyibPayStatus);

                if ($transaction->status !== $newStatus) {
                    $transaction->update([
                        'status' => $newStatus,
                        'toyyibpay_response_data' => array_merge(
                            $transaction->toyyibpay_response_data ?? [],
                            ['status_check' => $statusData]
                        ),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'transactionId' => $transaction->id,
                    'billCode' => $transaction->toyyibpay_billcode,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'paymentUrl' => $transaction->getPaymentUrl(),
                    'createdAt' => $transaction->created_at->toISOString(),
                    'updatedAt' => $transaction->updated_at->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel('toyyibpay_transactions')->error('Failed to get payment status', [
                'bill_code' => $billCode,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error while getting payment status',
            ], 500);
        }
    }

    /**
     * Validate ToyyibPay API key for configuration.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateApiKey(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'location_id' => 'required|string',
            'secret_key' => 'required|string',
            'category_code' => 'required|string',
            'mode' => 'required|in:sandbox,production',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            // Create temporary config for validation
            $tempConfig = new \App\Models\ToyyibPayConfig([
                'location_id' => $request->input('location_id'),
                'mode_active' => $request->input('mode'),
            ]);

            if ($request->input('mode') === 'production') {
                $tempConfig->secret_key_live = $request->input('secret_key');
                $tempConfig->category_code_live = $request->input('category_code');
            } else {
                $tempConfig->secret_key_sandbox = $request->input('secret_key');
                $tempConfig->category_code_sandbox = $request->input('category_code');
            }

            // Try to create a test bill to validate credentials
            $testBillData = [
                'bill_name' => 'API Key Validation Test',
                'bill_description' => 'Test bill for API key validation',
                'amount' => 1.00,
                'return_url' => url('/test'),
                'callback_url' => url('/test'),
                'external_reference_no' => 'validation_test_' . time(),
                'customer_name' => 'Test Customer',
                'customer_email' => 'test@example.com',
                'customer_phone' => '0123456789',
            ];

            $result = $this->toyyibPayService->createBill($tempConfig, $testBillData);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'API key validation successful',
                    'data' => [
                        'valid' => true,
                        'mode' => $request->input('mode'),
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'API key validation failed - invalid credentials',
                    'data' => [
                        'valid' => false,
                    ],
                ], 400);
            }

        } catch (\Exception $e) {
            Log::channel('toyyibpay_transactions')->error('API key validation failed', [
                'location_id' => $request->input('location_id'),
                'mode' => $request->input('mode'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'API key validation failed - server error',
                'data' => [
                    'valid' => false,
                ],
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