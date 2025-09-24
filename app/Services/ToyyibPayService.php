<?php

namespace App\Services;

use App\Models\Integration;
use App\Models\ToyyibPayConfig;
use App\Models\Transaction;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ToyyibPayService
{
    /**
     * HTTP client for API requests.
     */
    protected Client $client;

    /**
     * ToyyibPay configuration.
     */
    protected array $config;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => config('toyyibpay.timeout.request', 60),
            'connect_timeout' => config('toyyibpay.timeout.connection', 30),
        ]);

        $this->config = config('toyyibpay');
    }

    /**
     * Create bill/invoice in ToyyibPay.
     *
     * @param ToyyibPayConfig $toyyibPayConfig
     * @param array $billData
     * @return array|null
     */
    public function createBill(ToyyibPayConfig $toyyibPayConfig, array $billData): ?array
    {
        try {
            $baseUrl = $toyyibPayConfig->getApiBaseUrl();
            $secretKey = $toyyibPayConfig->getActiveSecretKey();
            $categoryCode = $toyyibPayConfig->getActiveCategoryCode();

            if (!$secretKey || !$categoryCode) {
                Log::channel('toyyibpay_transactions')->error('ToyyibPay configuration incomplete', [
                    'location_id' => $toyyibPayConfig->location_id,
                    'mode' => $toyyibPayConfig->mode_active,
                ]);
                return null;
            }

            Log::channel('toyyibpay_transactions')->info('Creating ToyyibPay bill', [
                'location_id' => $toyyibPayConfig->location_id,
                'amount' => $billData['amount'],
                'mode' => $toyyibPayConfig->mode_active,
            ]);

            $requestData = [
                'userSecretKey' => $secretKey,
                'categoryCode' => $categoryCode,
                'billName' => $billData['bill_name'],
                'billDescription' => $billData['bill_description'],
                'billPriceSetting' => 1, // Fixed price
                'billPayorInfo' => 1, // Enable payor info
                'billAmount' => number_format($billData['amount'], 2, '.', ''),
                'billReturnUrl' => $billData['return_url'],
                'billCallbackUrl' => $billData['callback_url'],
                'billExternalReferenceNo' => $billData['external_reference_no'],
                'billTo' => $billData['customer_name'] ?? '',
                'billEmail' => $billData['customer_email'] ?? '',
                'billPhone' => $billData['customer_phone'] ?? '',
                'billSplitPayment' => 0,
                'billSplitPaymentArgs' => '',
                'billPaymentChannel' => '0', // All channels
                'billContentEmail' => $billData['bill_description'],
                'billChargeToCustomer' => 1, // Charge fee to customer
            ];

            $response = $this->client->post($baseUrl . $this->config['endpoints']['create_bill'], [
                'form_params' => $requestData,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData[0]['BillCode'])) {
                Log::channel('toyyibpay_transactions')->info('ToyyibPay bill created successfully', [
                    'location_id' => $toyyibPayConfig->location_id,
                    'bill_code' => $responseData[0]['BillCode'],
                ]);

                return [
                    'bill_code' => $responseData[0]['BillCode'],
                    'bill_url' => $baseUrl . '/' . $responseData[0]['BillCode'],
                    'response_data' => $responseData,
                ];
            } else {
                Log::channel('toyyibpay_transactions')->error('ToyyibPay bill creation failed', [
                    'location_id' => $toyyibPayConfig->location_id,
                    'response' => $responseData,
                ]);
                return null;
            }

        } catch (GuzzleException $e) {
            Log::channel('toyyibpay_transactions')->error('ToyyibPay API request failed', [
                'location_id' => $toyyibPayConfig->location_id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return null;
        }
    }

    /**
     * Get bill status from ToyyibPay.
     *
     * @param ToyyibPayConfig $toyyibPayConfig
     * @param string $billCode
     * @return array|null
     */
    public function getBillStatus(ToyyibPayConfig $toyyibPayConfig, string $billCode): ?array
    {
        try {
            $baseUrl = $toyyibPayConfig->getApiBaseUrl();
            $secretKey = $toyyibPayConfig->getActiveSecretKey();

            Log::channel('toyyibpay_transactions')->info('Getting ToyyibPay bill status', [
                'location_id' => $toyyibPayConfig->location_id,
                'bill_code' => $billCode,
            ]);

            $response = $this->client->post($baseUrl . $this->config['endpoints']['bill_status'], [
                'form_params' => [
                    'billCode' => $billCode,
                    'billpaymentStatus' => '1', // Get payment status
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            Log::channel('toyyibpay_transactions')->info('ToyyibPay bill status retrieved', [
                'location_id' => $toyyibPayConfig->location_id,
                'bill_code' => $billCode,
                'status' => $responseData[0]['billpaymentStatus'] ?? 'unknown',
            ]);

            return $responseData;

        } catch (GuzzleException $e) {
            Log::channel('toyyibpay_transactions')->error('Failed to get ToyyibPay bill status', [
                'location_id' => $toyyibPayConfig->location_id,
                'bill_code' => $billCode,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return null;
        }
    }

    /**
     * Get bill information from ToyyibPay.
     *
     * @param ToyyibPayConfig $toyyibPayConfig
     * @param string $billCode
     * @return array|null
     */
    public function getBillInfo(ToyyibPayConfig $toyyibPayConfig, string $billCode): ?array
    {
        try {
            $baseUrl = $toyyibPayConfig->getApiBaseUrl();
            $secretKey = $toyyibPayConfig->getActiveSecretKey();

            Log::channel('toyyibpay_transactions')->info('Getting ToyyibPay bill info', [
                'location_id' => $toyyibPayConfig->location_id,
                'bill_code' => $billCode,
            ]);

            $response = $this->client->post($baseUrl . $this->config['endpoints']['get_bill'], [
                'form_params' => [
                    'billCode' => $billCode,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            Log::channel('toyyibpay_transactions')->info('ToyyibPay bill info retrieved', [
                'location_id' => $toyyibPayConfig->location_id,
                'bill_code' => $billCode,
            ]);

            return $responseData;

        } catch (GuzzleException $e) {
            Log::channel('toyyibpay_transactions')->error('Failed to get ToyyibPay bill info', [
                'location_id' => $toyyibPayConfig->location_id,
                'bill_code' => $billCode,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return null;
        }
    }

    /**
     * Process payment creation for GHL integration.
     *
     * @param string $locationId
     * @param array $paymentData
     * @return array|null
     */
    public function createPaymentForGHL(string $locationId, array $paymentData): ?array
    {
        try {
            // Get integration and ToyyibPay config
            $integration = Integration::where('location_id', $locationId)
                ->where('is_active', true)
                ->first();

            if (!$integration) {
                Log::channel('toyyibpay_transactions')->error('Integration not found', [
                    'location_id' => $locationId,
                ]);
                return null;
            }

            $toyyibPayConfig = $integration->toyyibPayConfig;

            if (!$toyyibPayConfig || !$toyyibPayConfig->isActiveModeConfigured()) {
                Log::channel('toyyibpay_transactions')->error('ToyyibPay not configured', [
                    'location_id' => $locationId,
                ]);
                return null;
            }

            // Create transaction record
            $transaction = Transaction::create([
                'location_id' => $locationId,
                'ghl_order_id' => $paymentData['order_id'],
                'ghl_transaction_id' => $paymentData['transaction_id'],
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'] ?? 'MYR',
                'description' => $paymentData['description'] ?? 'Payment via GHL',
                'customer_name' => $paymentData['customer_name'] ?? '',
                'customer_email' => $paymentData['customer_email'] ?? '',
                'customer_phone' => $paymentData['customer_phone'] ?? '',
                'status' => Transaction::STATUS_PENDING,
                'environment' => $toyyibPayConfig->mode_active,
                'toyyibpay_request_data' => $paymentData,
            ]);

            // Prepare bill data for ToyyibPay
            $billData = [
                'bill_name' => $paymentData['description'] ?? 'Payment via GHL',
                'bill_description' => $paymentData['description'] ?? 'Payment processing through GHL integration',
                'amount' => $paymentData['amount'],
                'return_url' => $paymentData['return_url'] ?? url('/payment/return'),
                'callback_url' => url('/api/toyyibpay/webhook/callback'),
                'external_reference_no' => $transaction->id,
                'customer_name' => $paymentData['customer_name'] ?? '',
                'customer_email' => $paymentData['customer_email'] ?? '',
                'customer_phone' => $paymentData['customer_phone'] ?? '',
            ];

            // Create bill in ToyyibPay
            $billResult = $this->createBill($toyyibPayConfig, $billData);

            if (!$billResult) {
                $transaction->markAsFailed();
                return null;
            }

            // Update transaction with ToyyibPay response
            $transaction->update([
                'toyyibpay_billcode' => $billResult['bill_code'],
                'toyyibpay_response_data' => $billResult['response_data'],
                'status' => Transaction::STATUS_PROCESSING,
            ]);

            return [
                'transaction_id' => $transaction->id,
                'bill_code' => $billResult['bill_code'],
                'payment_url' => $billResult['bill_url'],
                'status' => 'processing',
            ];

        } catch (\Exception $e) {
            Log::channel('toyyibpay_transactions')->error('Payment creation failed', [
                'location_id' => $locationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Handle ToyyibPay callback/webhook.
     *
     * @param array $callbackData
     * @return bool
     */
    public function handleCallback(array $callbackData): bool
    {
        try {
            $billCode = $callbackData['billcode'] ?? null;
            $status = $callbackData['status_id'] ?? null;

            if (!$billCode) {
                Log::channel('payment_webhooks')->error('ToyyibPay callback missing billcode', [
                    'callback_data' => $callbackData,
                ]);
                return false;
            }

            Log::channel('payment_webhooks')->info('ToyyibPay callback received', [
                'bill_code' => $billCode,
                'status_id' => $status,
            ]);

            // Find transaction by billcode
            $transaction = Transaction::where('toyyibpay_billcode', $billCode)->first();

            if (!$transaction) {
                Log::channel('payment_webhooks')->error('Transaction not found for billcode', [
                    'bill_code' => $billCode,
                ]);
                return false;
            }

            // Map ToyyibPay status to our status
            $newStatus = $this->mapToyyibPayStatus($status);

            // Update transaction status
            $transaction->update([
                'status' => $newStatus,
                'toyyibpay_callback_at' => now(),
                'toyyibpay_response_data' => array_merge(
                    $transaction->toyyibpay_response_data ?? [],
                    ['callback' => $callbackData]
                ),
            ]);

            Log::channel('payment_webhooks')->info('Transaction status updated', [
                'transaction_id' => $transaction->id,
                'old_status' => $transaction->getOriginal('status'),
                'new_status' => $newStatus,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::channel('payment_webhooks')->error('ToyyibPay callback processing failed', [
                'callback_data' => $callbackData,
                'error' => $e->getMessage(),
            ]);

            return false;
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

    /**
     * Validate ToyyibPay callback signature (if implemented by ToyyibPay).
     *
     * @param array $callbackData
     * @param string $signature
     * @return bool
     */
    public function validateCallbackSignature(array $callbackData, string $signature): bool
    {
        // ToyyibPay doesn't currently provide signature validation
        // This method is prepared for future implementation
        return true;
    }
}
