<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Link to integration
            $table->string('location_id')->comment('GHL Location ID');
            
            // GHL transaction identifiers
            $table->string('ghl_order_id')->nullable()->comment('GHL Order ID from payment request');
            $table->string('ghl_transaction_id')->nullable()->comment('GHL Transaction ID for tracking');
            
            // ToyyibPay transaction identifiers
            $table->string('toyyibpay_billcode')->nullable()->unique()->comment('ToyyibPay generated bill code');
            $table->string('toyyibpay_bill_id')->nullable()->comment('ToyyibPay bill ID');
            
            // Transaction details
            $table->decimal('amount', 10, 2)->comment('Transaction amount in MYR');
            $table->string('currency', 3)->default('MYR')->comment('Transaction currency');
            $table->text('description')->nullable()->comment('Payment description');
            
            // Customer information
            $table->string('customer_name')->nullable()->comment('Customer name');
            $table->string('customer_email')->nullable()->comment('Customer email');
            $table->string('customer_phone')->nullable()->comment('Customer phone');
            
            // Status tracking
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])
                  ->default('pending')
                  ->comment('Transaction status');
            
            // Environment tracking
            $table->enum('environment', ['sandbox', 'production'])->comment('Which environment was used');
            
            // Webhook and callback tracking
            $table->timestamp('toyyibpay_callback_at')->nullable()->comment('When ToyyibPay callback was received');
            $table->timestamp('ghl_notified_at')->nullable()->comment('When GHL was notified of status');
            
            // JSON data for debugging
            $table->json('toyyibpay_request_data')->nullable()->comment('Original ToyyibPay request data');
            $table->json('toyyibpay_response_data')->nullable()->comment('ToyyibPay response data');
            $table->json('ghl_webhook_data')->nullable()->comment('Data sent to GHL webhook');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('location_id');
            $table->index('ghl_order_id');
            $table->index('ghl_transaction_id');
            $table->index('toyyibpay_billcode');
            $table->index(['location_id', 'status']);
            $table->index(['status', 'created_at']);
            
            // Foreign key constraint
            $table->foreign('location_id')->references('location_id')->on('integrations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
