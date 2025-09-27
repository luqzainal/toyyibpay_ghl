<?php

namespace App\Services;

use App\Models\Integration;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GHLService
{
    /**
     * HTTP client for API requests.
     */
    protected Client $client;

    /**
     * GHL API base URL.
     */
    protected string $baseUrl;

    /**
     * GHL OAuth configuration.
     */
    protected array $config;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);

        $this->baseUrl = config('services.ghl.api_base_url');
        $this->config = config('services.ghl');
    }

    /**
     * Exchange authorization code for access token.
     *
     * @param string $authorizationCode
     * @return array|null
     */
    public function exchangeCodeForToken(string $authorizationCode): ?array
    {
        try {
            Log::channel('ghl_transactions')->info('Exchanging authorization code for token', [
                'code' => substr($authorizationCode, 0, 10) . '...',
            ]);

            $response = $this->client->post($this->baseUrl . '/oauth/token', [
                'form_params' => [
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret'],
                    'grant_type' => 'authorization_code',
                    'code' => $authorizationCode,
                    'redirect_uri' => $this->config['oauth_redirect'],
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::channel('ghl_transactions')->info('Token exchange successful', [
                'expires_in' => $data['expires_in'] ?? null,
                'token_type' => $data['token_type'] ?? null,
            ]);

            return $data;

        } catch (GuzzleException $e) {
            Log::channel('ghl_transactions')->error('Token exchange failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return null;
        }
    }

    /**
     * Refresh access token using refresh token.
     *
     * @param string $refreshToken
     * @return array|null
     */
    public function refreshAccessToken(string $refreshToken): ?array
    {
        try {
            Log::channel('ghl_transactions')->info('Refreshing access token');

            $response = $this->client->post($this->baseUrl . '/oauth/token', [
                'form_params' => [
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret'],
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::channel('ghl_transactions')->info('Token refresh successful');

            return $data;

        } catch (GuzzleException $e) {
            Log::channel('ghl_transactions')->error('Token refresh failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return null;
        }
    }

    /**
     * Get installed locations for the current access token.
     *
     * @param string $accessToken
     * @return array|null
     */
    public function getInstalledLocations(string $accessToken): ?array
    {
        try {
            Log::channel('ghl_transactions')->info('Getting installed locations');

            $response = $this->client->get($this->baseUrl . '/oauth/installedLocations', [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Accept' => 'application/json',
                    'Version' => '2021-07-28',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::channel('ghl_transactions')->info('Installed locations retrieved successfully', [
                'locations_count' => count($data['locations'] ?? []),
            ]);

            return $data;

        } catch (GuzzleException $e) {
            Log::channel('ghl_transactions')->error('Failed to get installed locations', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return null;
        }
    }

    /**
     * Get location information from GHL API.
     *
     * @param string $accessToken
     * @param string $locationId
     * @return array|null
     */
    public function getLocationInfo(string $accessToken, string $locationId): ?array
    {
        try {
            Log::channel('ghl_transactions')->info('Getting location info', [
                'location_id' => $locationId,
            ]);

            $response = $this->client->get($this->baseUrl . "/locations/{$locationId}", [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Accept' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::channel('ghl_transactions')->info('Location info retrieved successfully');

            return $data;

        } catch (GuzzleException $e) {
            Log::channel('ghl_transactions')->error('Failed to get location info', [
                'location_id' => $locationId,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return null;
        }
    }

    /**
     * Send payment status webhook to GHL.
     *
     * @param Integration $integration
     * @param array $paymentData
     * @return bool
     */
    public function sendPaymentStatusWebhook(Integration $integration, array $paymentData): bool
    {
        try {
            Log::channel('ghl_transactions')->info('Sending payment status to GHL', [
                'location_id' => $integration->location_id,
                'transaction_id' => $paymentData['ghl_transaction_id'] ?? null,
                'status' => $paymentData['status'] ?? null,
            ]);

            // Using correct GHL webhook endpoint from PRD
            $webhookUrl = 'https://backend.leadconnectorhq.com/payments/custom-provider/webhook';

            $response = $this->client->post($webhookUrl, [
                'json' => [
                    'event' => $paymentData['status'], // 'completed', 'failed', 'pending'
                    'chargeId' => $paymentData['charge_id'] ?? $paymentData['toyyibpay_billcode'],
                    'ghlTransactionId' => $paymentData['ghl_transaction_id'],
                    'chargeSnapshot' => [
                        'amount' => $paymentData['amount'],
                        'currency' => $paymentData['currency'] ?? 'MYR',
                        'status' => $paymentData['status'],
                        'paymentMethod' => 'toyyibpay',
                        'providerTransactionId' => $paymentData['provider_transaction_id'] ?? null,
                        'metadata' => $paymentData['metadata'] ?? [],
                    ],
                    'locationId' => $integration->location_id,
                    'apiKey' => $integration->api_key,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);

            Log::channel('ghl_transactions')->info('Payment status sent to GHL successfully', [
                'response_code' => $response->getStatusCode(),
            ]);

            return true;

        } catch (GuzzleException $e) {
            Log::channel('ghl_transactions')->error('Failed to send payment status to GHL', [
                'location_id' => $integration->location_id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return false;
        }
    }

    /**
     * Register as payment provider with GHL.
     *
     * @param Integration $integration
     * @return bool
     */
    public function registerPaymentProvider(Integration $integration): bool
    {
        try {
            Log::channel('ghl_transactions')->info('Registering payment provider with GHL', [
                'location_id' => $integration->location_id,
            ]);

            // Using correct GHL endpoint from PRD
            $response = $this->client->post($this->baseUrl . '/payments/custom-provider/provider', [
                'query' => [
                    'locationId' => $integration->location_id,
                ],
                'json' => [
                    'name' => 'ToyyibPay',
                    'description' => 'Malaysian online payment gateway by ToyyibPay',
                    'paymentsUrl' => url('/api/toyyibpay/create-payment'),
                    'queryUrl' => url('/api/ghl/query'),
                    'imageUrl' => url('/images/toyyibpay-logo.png'),
                ],
                'headers' => [
                    'Authorization' => "Bearer {$integration->access_token}",
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Version' => '2021-07-28',
                ],
            ]);

            Log::channel('ghl_transactions')->info('Payment provider registered successfully', [
                'response_code' => $response->getStatusCode(),
            ]);

            return true;

        } catch (GuzzleException $e) {
            Log::channel('ghl_transactions')->error('Failed to register payment provider', [
                'location_id' => $integration->location_id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return false;
        }
    }

    /**
     * Send connect keys to GHL for payment processing.
     *
     * @param Integration $integration
     * @return bool
     */
    public function sendConnectKeys(Integration $integration): bool
    {
        try {
            Log::channel('ghl_transactions')->info('Sending connect keys to GHL', [
                'location_id' => $integration->location_id,
            ]);

            // Generate publishable key for frontend use
            $publishableKey = $this->generatePublishableKey($integration->location_id);

            // Using correct GHL endpoint from PRD
            $response = $this->client->post($this->baseUrl . '/payments/custom-provider/connect', [
                'query' => [
                    'locationId' => $integration->location_id,
                ],
                'json' => [
                    'live' => [
                        'apiKey' => $integration->api_key,
                        'publishableKey' => $publishableKey,
                    ],
                    'test' => [
                        'apiKey' => $integration->api_key . '_test',
                        'publishableKey' => $publishableKey . '_test',
                    ],
                ],
                'headers' => [
                    'Authorization' => "Bearer {$integration->access_token}",
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Version' => '2021-07-28',
                ],
            ]);

            Log::channel('ghl_transactions')->info('Connect keys sent successfully', [
                'response_code' => $response->getStatusCode(),
            ]);

            return true;

        } catch (GuzzleException $e) {
            Log::channel('ghl_transactions')->error('Failed to send connect keys', [
                'location_id' => $integration->location_id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return false;
        }
    }

    /**
     * Generate unique API key for integration.
     *
     * @param string $locationId
     * @return string
     */
    public function generateApiKey(string $locationId): string
    {
        return 'ghl_toyyibpay_' . $locationId . '_' . Str::random(32);
    }

    /**
     * Generate publishable key for frontend use.
     *
     * @param string $locationId
     * @return string
     */
    public function generatePublishableKey(string $locationId): string
    {
        return 'pk_' . substr($locationId, 0, 8) . '_' . Str::random(24);
    }

    /**
     * Validate webhook signature from GHL.
     *
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->config['sso_key']);
        
        return hash_equals($expectedSignature, $signature);
    }
}
