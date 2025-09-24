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
        'location_id',
        'company_id',
        'access_token',
        'refresh_token',
        'api_key',
        'installed_at',
        'uninstalled_at',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'installed_at' => 'datetime',
        'uninstalled_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that should be encrypted.
     */
    protected $encrypted = [
        'access_token',
        'refresh_token',
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
        return $this->hasOne(ToyyibPayConfig::class, 'location_id', 'location_id');
    }

    /**
     * Get the transactions for this integration.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'location_id', 'location_id');
    }

    /**
     * Scope to get only active integrations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get integrations by location ID.
     */
    public function scopeByLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Check if the integration is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->access_token) 
            && !empty($this->refresh_token) 
            && !empty($this->api_key)
            && $this->is_active;
    }

    /**
     * Mark the integration as installed.
     */
    public function markAsInstalled(): void
    {
        $this->update([
            'installed_at' => now(),
            'uninstalled_at' => null,
            'is_active' => true,
        ]);
    }

    /**
     * Mark the integration as uninstalled.
     */
    public function markAsUninstalled(): void
    {
        $this->update([
            'uninstalled_at' => now(),
            'is_active' => false,
        ]);
    }
}
