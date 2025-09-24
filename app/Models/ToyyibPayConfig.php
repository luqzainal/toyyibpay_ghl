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
    protected $table = 'toyyibpay_config';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'location_id',
        'secret_key_live',
        'category_code_live',
        'secret_key_sandbox',
        'category_code_sandbox',
        'mode_active',
        'is_configured',
        'configured_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_configured' => 'boolean',
        'configured_at' => 'datetime',
    ];

    /**
     * The attributes that should be encrypted.
     */
    protected $encrypted = [
        'secret_key_live',
        'secret_key_sandbox',
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
        return $this->belongsTo(Integration::class, 'location_id', 'location_id');
    }

    /**
     * Get the active secret key based on current mode.
     */
    public function getActiveSecretKey(): ?string
    {
        return $this->mode_active === 'production' 
            ? $this->secret_key_live 
            : $this->secret_key_sandbox;
    }

    /**
     * Get the active category code based on current mode.
     */
    public function getActiveCategoryCode(): ?string
    {
        return $this->mode_active === 'production' 
            ? $this->category_code_live 
            : $this->category_code_sandbox;
    }

    /**
     * Get the API base URL based on current mode.
     */
    public function getApiBaseUrl(): string
    {
        return $this->mode_active === 'production'
            ? config('toyyibpay.production_url')
            : config('toyyibpay.sandbox_url');
    }

    /**
     * Check if the configuration is complete for the active mode.
     */
    public function isActiveModeConfigured(): bool
    {
        if ($this->mode_active === 'production') {
            return !empty($this->secret_key_live) && !empty($this->category_code_live);
        }
        
        return !empty($this->secret_key_sandbox) && !empty($this->category_code_sandbox);
    }

    /**
     * Switch to production mode.
     */
    public function switchToProduction(): void
    {
        $this->update(['mode_active' => 'production']);
    }

    /**
     * Switch to sandbox mode.
     */
    public function switchToSandbox(): void
    {
        $this->update(['mode_active' => 'sandbox']);
    }

    /**
     * Mark the configuration as completed.
     */
    public function markAsConfigured(): void
    {
        $this->update([
            'is_configured' => true,
            'configured_at' => now(),
        ]);
    }

    /**
     * Scope to get configurations by mode.
     */
    public function scopeByMode($query, $mode)
    {
        return $query->where('mode_active', $mode);
    }

    /**
     * Scope to get only configured ToyyibPay configs.
     */
    public function scopeConfigured($query)
    {
        return $query->where('is_configured', true);
    }
}
