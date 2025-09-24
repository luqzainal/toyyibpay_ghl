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
        Schema::create('toyyibpay_config', function (Blueprint $table) {
            $table->id();
            
            // Link to integration
            $table->string('location_id')->unique()->comment('GHL Location ID - links to integrations table');
            
            // Production/Live environment credentials (encrypted)
            $table->text('secret_key_live')->nullable()->comment('ToyyibPay secret key for live environment (encrypted)');
            $table->string('category_code_live')->nullable()->comment('ToyyibPay category code for live environment');
            
            // Sandbox/Test environment credentials (encrypted)
            $table->text('secret_key_sandbox')->nullable()->comment('ToyyibPay secret key for sandbox environment (encrypted)');
            $table->string('category_code_sandbox')->nullable()->comment('ToyyibPay category code for sandbox environment');
            
            // Active mode setting
            $table->enum('mode_active', ['sandbox', 'production'])->default('sandbox')->comment('Currently active ToyyibPay mode');
            
            // Configuration status
            $table->boolean('is_configured')->default(false)->comment('Is ToyyibPay properly configured for this location');
            $table->timestamp('configured_at')->nullable()->comment('When ToyyibPay was first configured');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('location_id');
            $table->index(['location_id', 'mode_active']);
            
            // Foreign key constraint
            $table->foreign('location_id')->references('location_id')->on('integrations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toyyibpay_config');
    }
};
