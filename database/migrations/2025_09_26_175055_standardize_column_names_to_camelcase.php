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
        // Update integrations table
        Schema::table('integrations', function (Blueprint $table) {
            $table->renameColumn('location_id', 'locationId');
            $table->renameColumn('company_id', 'companyId');
            $table->renameColumn('access_token', 'accessToken');
            $table->renameColumn('refresh_token', 'refreshToken');
            $table->renameColumn('api_key', 'apiKey');
            $table->renameColumn('installed_at', 'installedAt');
            $table->renameColumn('uninstalled_at', 'uninstalledAt');
            $table->renameColumn('is_active', 'isActive');
            $table->renameColumn('provider_registered', 'providerRegistered');
            $table->renameColumn('created_at', 'createdAt');
            $table->renameColumn('updated_at', 'updatedAt');
        });

        // Update toyyibpay_configs table
        Schema::table('toyyibpay_configs', function (Blueprint $table) {
            $table->renameColumn('location_id', 'locationId');
            $table->renameColumn('secret_key_live', 'secretKeyLive');
            $table->renameColumn('category_code_live', 'categoryCodeLive');
            $table->renameColumn('secret_key_sandbox', 'secretKeySandbox');
            $table->renameColumn('category_code_sandbox', 'categoryCodeSandbox');
            $table->renameColumn('mode_active', 'modeActive');
            $table->renameColumn('is_configured', 'isConfigured');
            $table->renameColumn('configured_at', 'configuredAt');
            $table->renameColumn('created_at', 'createdAt');
            $table->renameColumn('updated_at', 'updatedAt');
        });

        // Update transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('location_id', 'locationId');
            $table->renameColumn('ghl_order_id', 'ghlOrderId');
            $table->renameColumn('ghl_transaction_id', 'ghlTransactionId');
            $table->renameColumn('toyyibpay_billcode', 'toyyibpayBillcode');
            $table->renameColumn('toyyibpay_bill_id', 'toyyibpayBillId');
            $table->renameColumn('customer_name', 'customerName');
            $table->renameColumn('customer_email', 'customerEmail');
            $table->renameColumn('customer_phone', 'customerPhone');
            $table->renameColumn('toyyibpay_callback_at', 'toyyibpayCallbackAt');
            $table->renameColumn('ghl_notified_at', 'ghlNotifiedAt');
            $table->renameColumn('toyyibpay_request_data', 'toyyibpayRequestData');
            $table->renameColumn('toyyibpay_response_data', 'toyyibpayResponseData');
            $table->renameColumn('ghl_webhook_data', 'ghlWebhookData');
            $table->renameColumn('created_at', 'createdAt');
            $table->renameColumn('updated_at', 'updatedAt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert integrations table
        Schema::table('integrations', function (Blueprint $table) {
            $table->renameColumn('locationId', 'location_id');
            $table->renameColumn('companyId', 'company_id');
            $table->renameColumn('accessToken', 'access_token');
            $table->renameColumn('refreshToken', 'refresh_token');
            $table->renameColumn('apiKey', 'api_key');
            $table->renameColumn('installedAt', 'installed_at');
            $table->renameColumn('uninstalledAt', 'uninstalled_at');
            $table->renameColumn('isActive', 'is_active');
            $table->renameColumn('providerRegistered', 'provider_registered');
            $table->renameColumn('createdAt', 'created_at');
            $table->renameColumn('updatedAt', 'updated_at');
        });

        // Revert toyyibpay_configs table
        Schema::table('toyyibpay_configs', function (Blueprint $table) {
            $table->renameColumn('locationId', 'location_id');
            $table->renameColumn('secretKeyLive', 'secret_key_live');
            $table->renameColumn('categoryCodeLive', 'category_code_live');
            $table->renameColumn('secretKeySandbox', 'secret_key_sandbox');
            $table->renameColumn('categoryCodeSandbox', 'category_code_sandbox');
            $table->renameColumn('modeActive', 'mode_active');
            $table->renameColumn('isConfigured', 'is_configured');
            $table->renameColumn('configuredAt', 'configured_at');
            $table->renameColumn('createdAt', 'created_at');
            $table->renameColumn('updatedAt', 'updated_at');
        });

        // Revert transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('locationId', 'location_id');
            $table->renameColumn('ghlOrderId', 'ghl_order_id');
            $table->renameColumn('ghlTransactionId', 'ghl_transaction_id');
            $table->renameColumn('toyyibpayBillcode', 'toyyibpay_billcode');
            $table->renameColumn('toyyibpayBillId', 'toyyibpay_bill_id');
            $table->renameColumn('customerName', 'customer_name');
            $table->renameColumn('customerEmail', 'customer_email');
            $table->renameColumn('customerPhone', 'customer_phone');
            $table->renameColumn('toyyibpayCallbackAt', 'toyyibpay_callback_at');
            $table->renameColumn('ghlNotifiedAt', 'ghl_notified_at');
            $table->renameColumn('toyyibpayRequestData', 'toyyibpay_request_data');
            $table->renameColumn('toyyibpayResponseData', 'toyyibpay_response_data');
            $table->renameColumn('ghlWebhookData', 'ghl_webhook_data');
            $table->renameColumn('createdAt', 'created_at');
            $table->renameColumn('updatedAt', 'updated_at');
        });
    }
};
