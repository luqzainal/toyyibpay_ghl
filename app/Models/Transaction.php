<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'location_id',
        'ghl_order_id',
        'ghl_transaction_id',
        'toyyibpay_billcode',
        'toyyibpay_bill_id',
        'amount',
        'currency',
        'description',
        'customer_name',
        'customer_email',
        'customer_phone',
        'status',
        'environment',
        'toyyibpay_callback_at',
        'ghl_notified_at',
        'toyyibpay_request_data',
        'toyyibpay_response_data',
        'ghl_webhook_data',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'toyyibpay_callback_at' => 'datetime',
        'ghl_notified_at' => 'datetime',
        'toyyibpay_request_data' => 'array',
        'toyyibpay_response_data' => 'array',
        'ghl_webhook_data' => 'array',
    ];

    /**
     * Transaction status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    /**
     * Environment constants.
     */
    public const ENV_SANDBOX = 'sandbox';
    public const ENV_PRODUCTION = 'production';

    /**
     * Get the integration that owns this transaction.
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'location_id', 'location_id');
    }

    /**
     * Scope to get transactions by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get transactions by environment.
     */
    public function scopeByEnvironment($query, $environment)
    {
        return $query->where('environment', $environment);
    }

    /**
     * Scope to get pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope to get transactions by location ID.
     */
    public function scopeByLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Check if the transaction is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the transaction has failed.
     */
    public function isFailed(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_CANCELLED]);
    }

    /**
     * Mark the transaction as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'toyyibpay_callback_at' => now(),
        ]);
    }

    /**
     * Mark the transaction as failed.
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'toyyibpay_callback_at' => now(),
        ]);
    }

    /**
     * Mark the transaction as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    /**
     * Mark that GHL has been notified.
     */
    public function markGHLNotified(): void
    {
        $this->update(['ghl_notified_at' => now()]);
    }

    /**
     * Store ToyyibPay response data.
     */
    public function storeToyyibPayResponse(array $data): void
    {
        $this->update(['toyyibpay_response_data' => $data]);
    }

    /**
     * Store GHL webhook data.
     */
    public function storeGHLWebhookData(array $data): void
    {
        $this->update(['ghl_webhook_data' => $data]);
    }

    /**
     * Get the payment URL for this transaction.
     */
    public function getPaymentUrl(): ?string
    {
        if (empty($this->toyyibpay_billcode)) {
            return null;
        }

        $baseUrl = $this->environment === self::ENV_PRODUCTION
            ? config('toyyibpay.production_url')
            : config('toyyibpay.sandbox_url');

        return $baseUrl . '/' . $this->toyyibpay_billcode;
    }
}
