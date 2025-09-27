<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Integration extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'integrations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'locationId',
        'companyId',
        'accessToken',
        'refreshToken',
        'apiKey',
        'installedAt',
        'uninstalledAt',
        'isActive',
        'providerRegistered',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'installedAt' => 'datetime',
        'uninstalledAt' => 'datetime',
        'isActive' => 'boolean',
        'providerRegistered' => 'boolean',
    ];

    /**
     * The attributes that should be encrypted.
     */
    protected $encrypted = [
        'accessToken',
        'refreshToken',
    ];

    /**
     * Get the access token attribute (decrypt on retrieval).
     */
    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    /**
     * Get the refresh token attribute (decrypt on retrieval).
     */
    protected function refreshToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    /**
     * Get the ToyyibPay configuration for this integration.
     */
    public function toyyibPayConfig(): HasOne
    {
        return $this->hasOne(ToyyibPayConfig::class, 'locationId', 'locationId');
    }

    /**
     * Get the transactions for this integration.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'locationId', 'locationId');
    }

    /**
     * Scope to get only active integrations.
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', true);
    }

    /**
     * Scope to get integrations by location ID.
     */
    public function scopeByLocation($query, $locationId)
    {
        return $query->where('locationId', $locationId);
    }

    /**
     * Check if the integration is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->accessToken)
            && !empty($this->refreshToken)
            && !empty($this->apiKey)
            && $this->isActive;
    }

    /**
     * Mark the integration as installed.
     */
    public function markAsInstalled(): void
    {
        $this->update([
            'installedAt' => now(),
            'uninstalledAt' => null,
            'isActive' => true,
        ]);
    }

    /**
     * Mark the integration as uninstalled.
     */
    public function markAsUninstalled(): void
    {
        $this->update([
            'uninstalledAt' => now(),
            'isActive' => false,
        ]);
    }
}
