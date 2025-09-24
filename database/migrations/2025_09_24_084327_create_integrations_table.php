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
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            
            // GHL Integration identifiers
            $table->string('location_id')->unique()->comment('GHL Location ID');
            $table->string('company_id')->comment('GHL Company ID');
            
            // OAuth tokens (will be encrypted at model level)
            $table->text('access_token')->comment('GHL OAuth access token (encrypted)');
            $table->text('refresh_token')->comment('GHL OAuth refresh token (encrypted)');
            
            // Plugin-generated API key for this location
            $table->string('api_key')->unique()->comment('Unique API key for this location');
            
            // Installation tracking
            $table->timestamp('installed_at')->nullable()->comment('When the plugin was installed');
            $table->timestamp('uninstalled_at')->nullable()->comment('When the plugin was uninstalled');
            
            // Status tracking
            $table->boolean('is_active')->default(true)->comment('Is the integration currently active');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['location_id', 'is_active']);
            $table->index('company_id');
            $table->index('api_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
