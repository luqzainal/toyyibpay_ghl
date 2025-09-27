<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Services\GHLService;
use App\Services\KeyGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
    public function handleOAuthCallback(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'state' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            Log::channel('ghl_transactions')->error('OAuth callback validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);

            return redirect('/install-failure')->with([
                'error' => 'Invalid callback parameters: ' . $validator->errors()->first()
            ]);
        }

        try {
            // Exchange authorization code for access token
            $tokenData = $this->ghlService->exchangeCodeForToken($request->input('code'));

            if (!$tokenData) {
                return redirect('/install-failure')->with([
                    'error' => 'Failed to exchange authorization code for token'
                ]);
            }

            // First try to get locationId from token response
            $locationId = $tokenData['locationId'] ?? $tokenData['location_id'] ?? null;
            $companyId = $tokenData['companyId'] ?? $tokenData['company_id'] ?? null;

            // If locationId is not in token response, get installed locations
            if (!$locationId) {
                $installedLocations = $this->ghlService->getInstalledLocations($tokenData['access_token']);

                if (!$installedLocations || empty($installedLocations['locations'])) {
                    return redirect('/install-failure')->with([
                        'error' => 'Failed to retrieve location information'
                    ]);
                }

                // Get the first location (assuming single location OAuth)
                $firstLocation = $installedLocations['locations'][0];
                $locationId = $firstLocation['id'] ?? $firstLocation['locationId'];
                $companyId = $firstLocation['companyId'] ?? $firstLocation['company_id'];
            }

            // Validate that we have the required information
            if (!$locationId) {
                return redirect('/install-failure')->with([
                    'error' => 'Failed to retrieve location information - locationId not found'
                ]);
            }

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

            return redirect('/install-success')->with([
                'location_id' => $locationId,
                'message' => 'Integration installed successfully! You can now configure your ToyyibPay settings.'
            ]);

        } catch (\Exception $e) {
            Log::channel('ghl_transactions')->error('OAuth callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect('/install-failure')->with([
                'error' => 'Internal server error during OAuth callback'
            ]);
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

    /**
     * Get integration configuration for GHL Sub-Account tab.
     */
    public function getIntegrationConfig(Request $request): JsonResponse
    {
        $locationId = $request->input('location_id') ?? $request->query('locationId');

        if (!$locationId) {
            return response()->json([
                'success' => false,
                'message' => 'Location ID is required',
            ], 400);
        }

        try {
            $integration = Integration::where('location_id', $locationId)
                ->where('is_active', true)
                ->with('toyyibPayConfig')
                ->first();

            $isConfigured = $integration && $integration->toyyibPayConfig && $integration->toyyibPayConfig->is_configured;

            return response()->json([
                'success' => true,
                'data' => [
                    'name' => 'ToyyibPay',
                    'description' => 'Malaysian online payment gateway',
                    'is_configured' => $isConfigured,
                    'status' => $isConfigured ? 'connected' : 'not_configured',
                    'config_url' => url('/config?location_id=' . $locationId),
                    'logo_url' => url('/images/toyyibpay-logo.png'),
                    'provider_type' => 'payment_gateway',
                    'supported_currencies' => ['MYR'],
                    'supported_countries' => ['MY'],
                    'features' => [
                        'online_banking',
                        'e_wallet',
                        'credit_card',
                        'debit_card'
                    ],
                    'last_updated' => $integration?->updated_at?->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel('ghl_transactions')->error('Get integration config failed', [
                'location_id' => $locationId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve integration configuration',
            ], 500);
        }
    }

    /**
     * Auto-register integration when accessed from GHL.
     */
    public function autoRegisterIntegration(Request $request): JsonResponse
    {
        $locationId = $request->input('location_id') ?? $request->query('locationId');

        if (!$locationId) {
            return response()->json([
                'success' => false,
                'message' => 'Location ID is required',
            ], 400);
        }

        try {
            // Check if integration already exists
            $integration = Integration::where('location_id', $locationId)->first();

            if (!$integration) {
                // Create basic integration record
                $integration = Integration::create([
                    'location_id' => $locationId,
                    'is_active' => true,
                    'installed_at' => now(),
                ]);
            }

            // Try to register as payment provider if not already registered
            if (!$integration->provider_registered) {
                $registered = $this->ghlService->registerPaymentProvider($integration);

                if ($registered) {
                    $integration->update(['provider_registered' => true]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Integration registered successfully',
                'data' => [
                    'location_id' => $locationId,
                    'status' => 'registered',
                    'config_url' => url('/config?location_id=' . $locationId),
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel('ghl_transactions')->error('Auto registration failed', [
                'location_id' => $locationId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register integration',
            ], 500);
        }
    }
}
