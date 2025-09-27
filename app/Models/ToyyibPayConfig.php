<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ToyyibPayConfig extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'toyyibpay_configs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'locationId',
        'secretKeyLive',
        'categoryCodeLive',
        'secretKeySandbox',
        'categoryCodeSandbox',
        'modeActive',
        'isConfigured',
        'configuredAt',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'isConfigured' => 'boolean',
        'configuredAt' => 'datetime',
    ];

    /**
     * The attributes that should be encrypted.
     */
    protected $encrypted = [
        'secretKeyLive',
        'secretKeySandbox',
    ];

    /**
     * Get the secret key for live environment (decrypt on retrieval).
     */
    protected function secretKeyLive(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    /**
     * Get the secret key for sandbox environment (decrypt on retrieval).
     */
    protected function secretKeySandbox(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    /**
     * Get the integration that owns this configuration.
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'locationId', 'locationId');
    }

    /**
     * Get the active secret key based on current mode.
     */
    public function getActiveSecretKey(): ?string
    {
        return $this->modeActive === 'production'
            ? $this->secretKeyLive
            : $this->secretKeySandbox;
    }

    /**
     * Get the active category code based on current mode.
     */
    public function getActiveCategoryCode(): ?string
    {
        return $this->modeActive === 'production'
            ? $this->categoryCodeLive
            : $this->categoryCodeSandbox;
    }

    /**
     * Get the API base URL based on current mode.
     */
    public function getApiBaseUrl(): string
    {
        return $this->modeActive === 'production'
            ? config('toyyibpay.production_url')
            : config('toyyibpay.sandbox_url');
    }

    /**
     * Check if the configuration is complete for the active mode.
     */
    public function isActiveModeConfigured(): bool
    {
        if ($this->modeActive === 'production') {
            return !empty($this->secretKeyLive) && !empty($this->categoryCodeLive);
        }

        return !empty($this->secretKeySandbox) && !empty($this->categoryCodeSandbox);
    }

    /**
     * Switch to production mode.
     */
    public function switchToProduction(): void
    {
        $this->update(['modeActive' => 'production']);
    }

    /**
     * Switch to sandbox mode.
     */
    public function switchToSandbox(): void
    {
        $this->update(['modeActive' => 'sandbox']);
    }

    /**
     * Mark the configuration as completed.
     */
    public function markAsConfigured(): void
    {
        $this->update([
            'isConfigured' => true,
            'configuredAt' => now(),
        ]);
    }

    /**
     * Scope to get configurations by mode.
     */
    public function scopeByMode($query, $mode)
    {
        return $query->where('modeActive', $mode);
    }

    /**
     * Scope to get only configured ToyyibPay configs.
     */
    public function scopeConfigured($query)
    {
        return $query->where('isConfigured', true);
    }
}
