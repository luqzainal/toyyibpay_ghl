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
        'locationId',
        'ghlOrderId',
        'ghlTransactionId',
        'toyyibpayBillcode',
        'toyyibpayBillId',
        'amount',
        'currency',
        'description',
        'customerName',
        'customerEmail',
        'customerPhone',
        'status',
        'environment',
        'toyyibpayCallbackAt',
        'ghlNotifiedAt',
        'toyyibpayRequestData',
        'toyyibpayResponseData',
        'ghlWebhookData',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'toyyibpayCallbackAt' => 'datetime',
        'ghlNotifiedAt' => 'datetime',
        'toyyibpayRequestData' => 'array',
        'toyyibpayResponseData' => 'array',
        'ghlWebhookData' => 'array',
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
        return $this->belongsTo(Integration::class, 'locationId', 'locationId');
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
        return $query->where('locationId', $locationId);
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
            'toyyibpayCallbackAt' => now(),
        ]);
    }

    /**
     * Mark the transaction as failed.
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'toyyibpayCallbackAt' => now(),
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
        $this->update(['ghlNotifiedAt' => now()]);
    }

    /**
     * Store ToyyibPay response data.
     */
    public function storeToyyibPayResponse(array $data): void
    {
        $this->update(['toyyibpayResponseData' => $data]);
    }

    /**
     * Store GHL webhook data.
     */
    public function storeGHLWebhookData(array $data): void
    {
        $this->update(['ghlWebhookData' => $data]);
    }

    /**
     * Get the payment URL for this transaction.
     */
    public function getPaymentUrl(): ?string
    {
        if (empty($this->toyyibpayBillcode)) {
            return null;
        }

        $baseUrl = $this->environment === self::ENV_PRODUCTION
            ? config('toyyibpay.production_url')
            : config('toyyibpay.sandbox_url');

        return $baseUrl . '/' . $this->toyyibpayBillcode;
    }
}
