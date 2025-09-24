<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Services\GHLService;
use App\Services\KeyGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GHLController extends Controller
{
    protected GHLService $ghlService;
    protected KeyGeneratorService $keyGenerator;

    public function __construct(GHLService $ghlService, KeyGeneratorService $keyGenerator)
    {
        $this->ghlService = $ghlService;
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * Handle OAuth callback from GHL.
     */
    public function handleOAuthCallback(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'state' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            Log::channel('ghl_transactions')->error('OAuth callback validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid callback parameters',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            // Exchange authorization code for access token
            $tokenData = $this->ghlService->exchangeCodeForToken($request->input('code'));

            if (!$tokenData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to exchange authorization code for token',
                ], 400);
            }

            // Get location info to identify the location
            $locationInfo = $this->ghlService->getLocationInfo(
                $tokenData['access_token'],
                $tokenData['locationId'] ?? $tokenData['location_id'] ?? null
            );

            if (!$locationInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve location information',
                ], 400);
            }

            $locationId = $locationInfo['id'] ?? $locationInfo['locationId'];
            $companyId = $locationInfo['companyId'] ?? $locationInfo['company_id'];

            // Generate unique API key for this location
            $apiKey = $this->keyGenerator->generateUniqueKey($locationId);

            // Create or update integration record
            $integration = Integration::updateOrCreate(
                ['location_id' => $locationId],
                [
                    'company_id' => $companyId,
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'],
                    'api_key' => $apiKey,
                    'installed_at' => now(),
                    'is_active' => true,
                ]
            );

            Log::channel('ghl_transactions')->info('OAuth callback processed successfully', [
                'location_id' => $locationId,
                'company_id' => $companyId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Integration created successfully',
                'data' => [
                    'location_id' => $locationId,
                    'api_key' => $apiKey,
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel('ghl_transactions')->error('OAuth callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error during OAuth callback',
            ], 500);
        }
    }

    /**
     * Register provider with GHL marketplace.
     */
    public function registerProvider(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'location_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $integration = Integration::where('location_id', $request->input('location_id'))
                ->where('is_active', true)
                ->first();

            if (!$integration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integration not found or inactive',
                ], 404);
            }

            // Register as payment provider
            $success = $this->ghlService->registerPaymentProvider($integration);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to register payment provider',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment provider registered successfully',
            ]);

        } catch (\Exception $e) {
            Log::channel('ghl_transactions')->error('Provider registration failed', [
                'location_id' => $request->input('location_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error during provider registration',
            ], 500);
        }
    }

    /**
     * Connect keys endpoint to send plugin-generated keys to GHL.
     */
    public function connectKeys(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'location_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $integration = Integration::where('location_id', $request->input('location_id'))
                ->where('is_active', true)
                ->first();

            if (!$integration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integration not found or inactive',
                ], 404);
            }

            // Send connect keys to GHL
            $success = $this->ghlService->sendConnectKeys($integration);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send connect keys',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Connect keys sent successfully',
                'data' => [
                    'api_key' => $integration->api_key,
                    'is_live' => $integration->toyyibPayConfig?->mode_active === 'production',
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel('ghl_transactions')->error('Connect keys failed', [
                'location_id' => $request->input('location_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error during connect keys',
            ], 500);
        }
    }

    /**
     * Handle plugin install webhook from GHL.
     */
    public function handleInstall(Request $request): JsonResponse
    {
        try {
            $locationId = $request->input('locationId');
            $companyId = $request->input('companyId');

            Log::channel('ghl_transactions')->info('Plugin install webhook received', [
                'location_id' => $locationId,
                'company_id' => $companyId,
            ]);

            $integration = Integration::where('location_id', $locationId)->first();

            if ($integration) {
                $integration->markAsInstalled();
                
                Log::channel('ghl_transactions')->info('Plugin marked as installed', [
                    'location_id' => $locationId,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Install webhook processed successfully',
            ]);

        } catch (\Exception $e) {
            Log::channel('ghl_transactions')->error('Install webhook failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process install webhook',
            ], 500);
        }
    }

    /**
     * Handle plugin uninstall webhook from GHL.
     */
    public function handleUninstall(Request $request): JsonResponse
    {
        try {
            $locationId = $request->input('locationId');

            Log::channel('ghl_transactions')->info('Plugin uninstall webhook received', [
                'location_id' => $locationId,
            ]);

            $integration = Integration::where('location_id', $locationId)->first();

            if ($integration) {
                $integration->markAsUninstalled();
                
                Log::channel('ghl_transactions')->info('Plugin marked as uninstalled', [
                    'location_id' => $locationId,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Uninstall webhook processed successfully',
            ]);

        } catch (\Exception $e) {
            Log::channel('ghl_transactions')->error('Uninstall webhook failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process uninstall webhook',
            ], 500);
        }
    }

    /**
     * Update payment status to GHL.
     */
    public function updatePaymentStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'location_id' => 'required|string',
            'ghl_transaction_id' => 'required|string',
            'toyyibpay_billcode' => 'required|string',
            'status' => 'required|in:completed,failed,pending',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $integration = Integration::where('location_id', $request->input('location_id'))
                ->where('is_active', true)
                ->first();

            if (!$integration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integration not found or inactive',
                ], 404);
            }

            // Send payment status to GHL using correct parameter structure
            $success = $this->ghlService->sendPaymentStatusWebhook($integration, [
                'ghl_transaction_id' => $request->input('ghl_transaction_id'),
                'toyyibpay_billcode' => $request->input('toyyibpay_billcode'),
                'charge_id' => $request->input('toyyibpay_billcode'),
                'status' => $request->input('status'),
                'amount' => $request->input('amount'),
                'currency' => $request->input('currency', 'MYR'),
                'provider_transaction_id' => $request->input('provider_transaction_id'),
                'metadata' => $request->input('metadata', []),
            ]);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update payment status',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully',
            ]);

        } catch (\Exception $e) {
            Log::channel('ghl_transactions')->error('Payment status update failed', [
                'location_id' => $request->input('location_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error during payment status update',
            ], 500);
        }
    }

    /**
     * Query/verification endpoint that returns success: false as required by GHL.
     */
    public function queryEndpoint(Request $request): JsonResponse
    {
        // As per GHL requirements, this endpoint should return success: false
        return response()->json([
            'success' => false,
            'message' => 'Query endpoint - verification only',
        ]);
    }
}
