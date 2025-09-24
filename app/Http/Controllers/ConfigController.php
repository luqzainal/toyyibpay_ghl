<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\ToyyibPayConfig;
use App\Services\ToyyibPayService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
    protected ToyyibPayService $toyyibPayService;

    public function __construct(ToyyibPayService $toyyibPayService)
    {
        $this->toyyibPayService = $toyyibPayService;
    }

    /**
     * Save ToyyibPay configuration for a location.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveConfiguration(Request $request): JsonResponse
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
                'message' => 'Invalid configuration parameters',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $locationId = $request->input('location_id');
            
            // Check if integration exists
            $integration = Integration::where('location_id', $locationId)
                ->where('is_active', true)
                ->first();

            if (!$integration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integration not found or inactive',
                ], 404);
            }

            // Create or update ToyyibPay configuration
            $config = ToyyibPayConfig::updateOrCreate(
                ['location_id' => $locationId],
                [
                    'mode_active' => $request->input('mode'),
                    'is_configured' => true,
                    'configured_at' => now(),
                ]
            );

            // Set the appropriate credentials based on mode
            if ($request->input('mode') === 'production') {
                $config->update([
                    'secret_key_live' => $request->input('secret_key'),
                    'category_code_live' => $request->input('category_code'),
                ]);
            } else {
                $config->update([
                    'secret_key_sandbox' => $request->input('secret_key'),
                    'category_code_sandbox' => $request->input('category_code'),
                ]);
            }

            Log::channel('toyyibpay_transactions')->info('ToyyibPay configuration saved', [
                'location_id' => $locationId,
                'mode' => $request->input('mode'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuration saved successfully',
                'data' => [
                    'location_id' => $locationId,
                    'mode_active' => $config->mode_active,
                    'is_configured' => $config->is_configured,
                    'configured_at' => $config->configured_at,
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel('toyyibpay_transactions')->error('Configuration save failed', [
                'location_id' => $request->input('location_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error while saving configuration',
            ], 500);
        }
    }

    /**
     * Get ToyyibPay configuration for a location.
     *
     * @param string $locationId
     * @return JsonResponse
     */
    public function getConfiguration(string $locationId): JsonResponse
    {
        try {
            $config = ToyyibPayConfig::where('location_id', $locationId)->first();

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'location_id' => $config->location_id,
                    'mode_active' => $config->mode_active,
                    'is_configured' => $config->is_configured,
                    'configured_at' => $config->configured_at,
                    'has_live_credentials' => !empty($config->secret_key_live) && !empty($config->category_code_live),
                    'has_sandbox_credentials' => !empty($config->secret_key_sandbox) && !empty($config->category_code_sandbox),
                    'created_at' => $config->created_at,
                    'updated_at' => $config->updated_at,
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel('toyyibpay_transactions')->error('Configuration retrieval failed', [
                'location_id' => $locationId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error while retrieving configuration',
            ], 500);
        }
    }

    /**
     * Update configuration mode (sandbox/production).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateMode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'location_id' => 'required|string',
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
            $config = ToyyibPayConfig::where('location_id', $request->input('location_id'))->first();

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration not found',
                ], 404);
            }

            $newMode = $request->input('mode');

            // Check if the new mode has the required credentials
            if ($newMode === 'production' && (empty($config->secret_key_live) || empty($config->category_code_live))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Production credentials not configured',
                ], 400);
            }

            if ($newMode === 'sandbox' && (empty($config->secret_key_sandbox) || empty($config->category_code_sandbox))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sandbox credentials not configured',
                ], 400);
            }

            $config->update(['mode_active' => $newMode]);

            Log::channel('toyyibpay_transactions')->info('ToyyibPay mode updated', [
                'location_id' => $request->input('location_id'),
                'new_mode' => $newMode,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mode updated successfully',
                'data' => [
                    'location_id' => $config->location_id,
                    'mode_active' => $config->mode_active,
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel('toyyibpay_transactions')->error('Mode update failed', [
                'location_id' => $request->input('location_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error while updating mode',
            ], 500);
        }
    }

    /**
     * Delete configuration for a location.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteConfiguration(Request $request): JsonResponse
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
            $locationId = $request->input('location_id');
            $config = ToyyibPayConfig::where('location_id', $locationId)->first();

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration not found',
                ], 404);
            }

            $config->delete();

            Log::channel('toyyibpay_transactions')->info('ToyyibPay configuration deleted', [
                'location_id' => $locationId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuration deleted successfully',
            ]);

        } catch (\Exception $e) {
            Log::channel('toyyibpay_transactions')->error('Configuration deletion failed', [
                'location_id' => $request->input('location_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error while deleting configuration',
            ], 500);
        }
    }
}