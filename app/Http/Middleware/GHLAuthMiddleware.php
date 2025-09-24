<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GHLAuthMiddleware
{
    /**
     * Handle an incoming request for GHL API authentication.
     *
     * This middleware validates Bearer tokens and SSO keys from GHL
     * to ensure secure communication between GHL and our plugin.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for Bearer token in Authorization header
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Bearer token is required'
            ], 401);
        }

        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

        // For webhook endpoints, validate against SSO key
        if ($request->is('api/ghl/webhook/*')) {
            $expectedSsoKey = config('services.ghl.sso_key');
            
            if (empty($expectedSsoKey)) {
                return response()->json([
                    'error' => 'Configuration Error',
                    'message' => 'GHL SSO key not configured'
                ], 500);
            }

            if ($token !== $expectedSsoKey) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Invalid SSO key'
                ], 401);
            }
        }

        // For API endpoints, we'll validate the token against stored integration data
        // This will be implemented when we have the Integration model ready
        
        // Add the token to the request for use in controllers
        $request->merge(['ghl_token' => $token]);

        return $next($request);
    }
}
