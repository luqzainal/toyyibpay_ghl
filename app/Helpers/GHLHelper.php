<?php

use App\Models\Integration;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

if (!function_exists('ghl_oauth_call')) {
    /**
     * Make OAuth call to GHL API
     */
    function ghl_oauth_call($code = '', $method = '')
    {
        $cred = getAppCredentials();

        if (!$cred) {
            Log::error('GHL OAuth Call - No app credentials found');
            return null;
        }

        $url = 'https://services.leadconnectorhq.com/oauth/token';

        $data = [
            'client_id' => $cred['client_id'],
            'client_secret' => $cred['client_secret'],
            'grant_type' => empty($method) ? 'authorization_code' : 'refresh_token'
        ];

        if (empty($method)) {
            $data['code'] = $code;
        } else {
            $data['refresh_token'] = $code;
        }

        try {
            $client = new Client(['timeout' => 30]);

            $response = $client->post($url, [
                'form_params' => $data,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            Log::channel('ghl_transactions')->info('GHL OAuth call successful', [
                'method' => $method ?: 'authorization_code',
                'has_access_token' => isset($result['access_token'])
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::channel('ghl_transactions')->error('GHL OAuth call failed', [
                'error' => $e->getMessage(),
                'method' => $method ?: 'authorization_code'
            ]);
            return null;
        }
    }
}

if (!function_exists('ghl_token')) {
    /**
     * Get GHL token from request
     */
    function ghl_token($request, $type = '')
    {
        $code = $request->code ?? $request->input('code');

        if (!$code) {
            Log::error('GHL Token - No authorization code provided');
            if (empty($type)) {
                abort(400, 'Authorization code required');
            }
            return null;
        }

        $tokenData = ghl_oauth_call($code, $type);

        if ($tokenData && isset($tokenData['access_token'])) {
            return (object) $tokenData;
        }

        if (isset($tokenData['error_description'])) {
            Log::error('GHL Token Error', ['error' => $tokenData['error_description']]);
            if (empty($type)) {
                abort(400, $tokenData['error_description']);
            }
        }

        if (empty($type)) {
            abort(500, 'Failed to obtain access token');
        }

        return null;
    }
}

if (!function_exists('ghl_api_call')) {
    /**
     * Make API call to GHL
     */
    function ghl_api_call($url, $method, $locationId, $data = null, $json = false)
    {
        $baseurl = 'https://services.leadconnectorhq.com/';

        $integration = getLocationIntegration($locationId);
        if (!$integration) {
            Log::error('GHL API Call - No integration found', ['location_id' => $locationId]);
            return null;
        }

        $bearer = 'Bearer ' . $integration->access_token;
        $version = config('services.ghl.api_version', '2021-07-28');

        $headers = [
            'Authorization' => $bearer,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Version' => $version,
        ];

        // Add locationId to GET requests
        if (strtolower($method) === 'get') {
            $separator = strpos($url, '?') !== false ? '&' : '?';
            if (strpos($url, 'locationId=') === false) {
                $url .= $separator . 'locationId=' . $locationId;
            }
        }

        try {
            $client = new Client([
                'http_errors' => false,
                'timeout' => 30,
                'headers' => $headers
            ]);

            $options = [];
            if (!empty($data)) {
                $options['json'] = $data;
            }

            $fullUrl = $baseurl . ltrim($url, '/');
            $response = $client->request(strtoupper($method), $fullUrl, $options);
            $responseBody = json_decode($response->getBody()->getContents(), true);

            // Handle token refresh if unauthorized
            if (isset($responseBody['error']) &&
                ($responseBody['error'] === 'Unauthorized' ||
                 (isset($responseBody['message']) && $responseBody['message'] === 'Invalid JWT'))) {

                Log::info('GHL API - Token expired, refreshing', ['location_id' => $locationId]);

                $newTokenData = refreshLocationToken($integration);
                if ($newTokenData) {
                    // Retry the API call with new token
                    return ghl_api_call($url, $method, $locationId, $data, $json);
                }
            }

            Log::channel('ghl_transactions')->info('GHL API call completed', [
                'url' => $fullUrl,
                'method' => $method,
                'status_code' => $response->getStatusCode()
            ]);

            return $json ? json_encode($responseBody) : (object) $responseBody;

        } catch (\Exception $e) {
            Log::channel('ghl_transactions')->error('GHL API call failed', [
                'url' => $baseurl . $url,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}

if (!function_exists('getLocationIntegration')) {
    /**
     * Get location integration data
     */
    function getLocationIntegration($locationId)
    {
        $integration = Integration::where('location_id', $locationId)
            ->where('is_active', true)
            ->first();

        if (!$integration) {
            Log::warning('No active integration found', ['location_id' => $locationId]);
            return null;
        }

        return $integration;
    }
}

if (!function_exists('refreshLocationToken')) {
    /**
     * Refresh access token for location
     */
    function refreshLocationToken($integration)
    {
        try {
            $tokenData = ghl_oauth_call($integration->refresh_token, 'refresh_token');

            if ($tokenData && isset($tokenData['access_token'])) {
                // Update integration with new tokens
                $integration->update([
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'] ?? $integration->refresh_token,
                ]);

                Log::channel('ghl_transactions')->info('Token refreshed successfully', [
                    'location_id' => $integration->location_id
                ]);

                return $tokenData;
            }

            Log::error('Failed to refresh token', [
                'location_id' => $integration->location_id,
                'error' => $tokenData['error'] ?? 'Unknown error'
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Token refresh exception', [
                'location_id' => $integration->location_id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}

if (!function_exists('getAppCredentials')) {
    /**
     * Get app credentials for GHL
     */
    function getAppCredentials()
    {
        return [
            'client_id' => config('services.ghl.client_id'),
            'client_secret' => config('services.ghl.client_secret'),
            'oauth_redirect' => config('services.ghl.oauth_redirect'),
        ];
    }
}

if (!function_exists('getLocationCredentials')) {
    /**
     * Get ToyyibPay credentials for location
     */
    function getLocationCredentials($locationId)
    {
        $integration = Integration::where('location_id', $locationId)
            ->where('is_active', true)
            ->with('toyyibPayConfig')
            ->first();

        if (!$integration || !$integration->toyyibPayConfig) {
            return null;
        }

        return $integration->toyyibPayConfig;
    }
}

if (!function_exists('registerGHLProvider')) {
    /**
     * Register ToyyibPay as payment provider in GHL
     */
    function registerGHLProvider($locationId)
    {
        $integration = getLocationIntegration($locationId);
        if (!$integration) {
            return false;
        }

        $providerData = [
            'name' => 'ToyyibPay',
            'description' => 'Malaysian online payment gateway by ToyyibPay',
            'paymentsUrl' => url('/api/toyyibpay/create-payment'),
            'queryUrl' => url('/api/ghl/query'),
            'imageUrl' => url('/images/toyyibpay-logo.png'),
        ];

        $response = ghl_api_call(
            'payments/custom-provider/provider?locationId=' . $locationId,
            'POST',
            $providerData,
            $locationId
        );

        return $response && !isset($response->error);
    }
}

if (!function_exists('sendGHLConnectKeys')) {
    /**
     * Send connect keys to GHL
     */
    function sendGHLConnectKeys($locationId)
    {
        $integration = getLocationIntegration($locationId);
        if (!$integration) {
            return false;
        }

        // Generate publishable key
        $publishableKey = 'pk_' . substr($locationId, 0, 8) . '_' . \Illuminate\Support\Str::random(24);

        $keysData = [
            'live' => [
                'apiKey' => $integration->api_key,
                'publishableKey' => $publishableKey,
            ],
            'test' => [
                'apiKey' => $integration->api_key . '_test',
                'publishableKey' => $publishableKey . '_test',
            ],
        ];

        $response = ghl_api_call(
            'payments/custom-provider/connect?locationId=' . $locationId,
            'POST',
            $keysData,
            $locationId
        );

        return $response && !isset($response->error);
    }
}