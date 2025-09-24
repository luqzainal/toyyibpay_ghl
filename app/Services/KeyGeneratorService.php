<?php

namespace App\Services;

use App\Models\Integration;
use Illuminate\Support\Str;

class KeyGeneratorService
{
    /**
     * Generate a unique API key for a location.
     *
     * @param string $locationId
     * @return string
     */
    public function generateUniqueKey(string $locationId): string
    {
        do {
            // Generate key with format: ghl_toyyibpay_{location_id}_{random}
            $key = 'ghl_toyyibpay_' . substr($locationId, 0, 8) . '_' . Str::random(32);
            
            // Check if key already exists
            $exists = Integration::where('api_key', $key)->exists();
            
        } while ($exists);

        return $key;
    }

    /**
     * Generate a publishable key for frontend use.
     *
     * @param string $locationId
     * @return string
     */
    public function generatePublishableKey(string $locationId): string
    {
        return 'pk_' . substr($locationId, 0, 8) . '_' . Str::random(24);
    }

    /**
     * Validate API key format.
     *
     * @param string $key
     * @return bool
     */
    public function isValidKeyFormat(string $key): bool
    {
        return preg_match('/^ghl_toyyibpay_[a-zA-Z0-9]{8}_[a-zA-Z0-9]{32}$/', $key) === 1;
    }

    /**
     * Extract location ID from API key.
     *
     * @param string $key
     * @return string|null
     */
    public function extractLocationIdFromKey(string $key): ?string
    {
        if (!$this->isValidKeyFormat($key)) {
            return null;
        }

        $parts = explode('_', $key);
        return $parts[2] ?? null;
    }
}
