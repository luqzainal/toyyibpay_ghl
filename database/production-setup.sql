-- ================================================================
-- GHL ToyyibPay Integration - Production Database Setup
-- ================================================================
--
-- This SQL file creates all necessary tables and indexes for the
-- GHL ToyyibPay integration plugin in production environment.
--
-- Run this file in your production MySQL database.
--
-- Database: gohl_toyyibpay (or your chosen database name)
-- ================================================================

-- Create database if it doesn't exist
-- CREATE DATABASE IF NOT EXISTS gohl_toyyibpay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE gohl_toyyibpay;

-- ================================================================
-- Table: integrations
-- ================================================================
-- Stores GHL OAuth integration data and plugin API keys
-- ================================================================

DROP TABLE IF EXISTS integrations;

CREATE TABLE integrations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

    -- GHL Integration identifiers
    location_id VARCHAR(255) NOT NULL UNIQUE COMMENT 'GHL Location ID',
    company_id VARCHAR(255) NOT NULL COMMENT 'GHL Company ID',

    -- OAuth tokens (will be encrypted at application level)
    access_token TEXT NOT NULL COMMENT 'GHL OAuth access token (encrypted)',
    refresh_token TEXT NOT NULL COMMENT 'GHL OAuth refresh token (encrypted)',

    -- Plugin-generated API key for this location
    api_key VARCHAR(255) NOT NULL UNIQUE COMMENT 'Unique API key for this location',

    -- Installation tracking
    installed_at TIMESTAMP NULL COMMENT 'When the plugin was installed',
    uninstalled_at TIMESTAMP NULL COMMENT 'When the plugin was uninstalled',

    -- Status tracking
    is_active BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Is the integration currently active',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes for performance
    INDEX idx_location_id_active (location_id, is_active),
    INDEX idx_company_id (company_id),
    INDEX idx_api_key (api_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='GHL OAuth integrations and API keys';

-- ================================================================
-- Table: toyyibpay_configs
-- ================================================================
-- Stores ToyyibPay configuration for each GHL location
-- ================================================================

DROP TABLE IF EXISTS toyyibpay_configs;

CREATE TABLE toyyibpay_configs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

    -- Link to integration
    location_id VARCHAR(255) NOT NULL UNIQUE COMMENT 'GHL Location ID - links to integrations table',

    -- Production/Live environment credentials (encrypted)
    secret_key_live TEXT NULL COMMENT 'ToyyibPay secret key for live environment (encrypted)',
    category_code_live VARCHAR(255) NULL COMMENT 'ToyyibPay category code for live environment',

    -- Sandbox/Test environment credentials (encrypted)
    secret_key_sandbox TEXT NULL COMMENT 'ToyyibPay secret key for sandbox environment (encrypted)',
    category_code_sandbox VARCHAR(255) NULL COMMENT 'ToyyibPay category code for sandbox environment',

    -- Active mode setting
    mode_active ENUM('sandbox', 'production') NOT NULL DEFAULT 'sandbox' COMMENT 'Currently active ToyyibPay mode',

    -- Configuration status
    is_configured BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Is ToyyibPay properly configured for this location',
    configured_at TIMESTAMP NULL COMMENT 'When ToyyibPay was first configured',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes for performance
    INDEX idx_location_id (location_id),
    INDEX idx_location_mode (location_id, mode_active),

    -- Foreign key constraint
    CONSTRAINT fk_toyyibpay_configs_location
        FOREIGN KEY (location_id)
        REFERENCES integrations(location_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ToyyibPay configuration per GHL location';

-- ================================================================
-- Table: transactions
-- ================================================================
-- Stores all payment transactions between GHL and ToyyibPay
-- ================================================================

DROP TABLE IF EXISTS transactions;

CREATE TABLE transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

    -- Link to integration
    location_id VARCHAR(255) NOT NULL COMMENT 'GHL Location ID',

    -- GHL transaction identifiers
    ghl_order_id VARCHAR(255) NULL COMMENT 'GHL Order ID from payment request',
    ghl_transaction_id VARCHAR(255) NULL COMMENT 'GHL Transaction ID for tracking',

    -- ToyyibPay transaction identifiers
    toyyibpay_billcode VARCHAR(255) NULL UNIQUE COMMENT 'ToyyibPay generated bill code',
    toyyibpay_bill_id VARCHAR(255) NULL COMMENT 'ToyyibPay bill ID',

    -- Transaction details
    amount DECIMAL(10,2) NOT NULL COMMENT 'Transaction amount in MYR',
    currency VARCHAR(3) NOT NULL DEFAULT 'MYR' COMMENT 'Transaction currency',
    description TEXT NULL COMMENT 'Payment description',

    -- Customer information
    customer_name VARCHAR(255) NULL COMMENT 'Customer name',
    customer_email VARCHAR(255) NULL COMMENT 'Customer email',
    customer_phone VARCHAR(255) NULL COMMENT 'Customer phone',

    -- Status tracking
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded')
           NOT NULL DEFAULT 'pending' COMMENT 'Transaction status',

    -- Environment tracking
    environment ENUM('sandbox', 'production') NOT NULL COMMENT 'Which environment was used',

    -- Webhook and callback tracking
    toyyibpay_callback_at TIMESTAMP NULL COMMENT 'When ToyyibPay callback was received',
    ghl_notified_at TIMESTAMP NULL COMMENT 'When GHL was notified of status',

    -- JSON data for debugging (stored as JSON for MySQL 5.7+)
    toyyibpay_request_data JSON NULL COMMENT 'Original ToyyibPay request data',
    toyyibpay_response_data JSON NULL COMMENT 'ToyyibPay response data',
    ghl_webhook_data JSON NULL COMMENT 'Data sent to GHL webhook',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes for performance
    INDEX idx_location_id (location_id),
    INDEX idx_ghl_order_id (ghl_order_id),
    INDEX idx_ghl_transaction_id (ghl_transaction_id),
    INDEX idx_toyyibpay_billcode (toyyibpay_billcode),
    INDEX idx_location_status (location_id, status),
    INDEX idx_status_created (status, created_at),

    -- Foreign key constraint
    CONSTRAINT fk_transactions_location
        FOREIGN KEY (location_id)
        REFERENCES integrations(location_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payment transactions between GHL and ToyyibPay';

-- ================================================================
-- Additional Production Optimizations
-- ================================================================

-- Add additional indexes for common queries
ALTER TABLE transactions ADD INDEX idx_amount_status (amount, status);
ALTER TABLE transactions ADD INDEX idx_customer_email (customer_email);
ALTER TABLE transactions ADD INDEX idx_environment_status (environment, status);

-- Add index for API key lookups
ALTER TABLE integrations ADD INDEX idx_api_key_active (api_key, is_active);

-- ================================================================
-- Sample Data (Optional - Remove in production)
-- ================================================================
-- Uncomment the following if you want to insert sample data for testing

/*
-- Sample integration (replace with real data)
INSERT INTO integrations (
    location_id,
    company_id,
    access_token,
    refresh_token,
    api_key,
    installed_at,
    is_active
) VALUES (
    'sample_location_123',
    'sample_company_456',
    'encrypted_access_token_here',
    'encrypted_refresh_token_here',
    'ghl_toyyibpay_sample_123_' + MD5(RAND()),
    NOW(),
    TRUE
);

-- Sample ToyyibPay config (replace with real data)
INSERT INTO toyyibpay_configs (
    location_id,
    secret_key_sandbox,
    category_code_sandbox,
    mode_active,
    is_configured,
    configured_at
) VALUES (
    'sample_location_123',
    'encrypted_sandbox_key_here',
    'sandbox_category_code',
    'sandbox',
    TRUE,
    NOW()
);
*/

-- ================================================================
-- Permissions and User Setup (Optional)
-- ================================================================
-- Create a dedicated database user for the application
-- Uncomment and modify as needed:

/*
-- Create application user
CREATE USER IF NOT EXISTS 'gohl_toyyibpay_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';

-- Grant necessary permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON gohl_toyyibpay.* TO 'gohl_toyyibpay_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;
*/

-- ================================================================
-- Verification Queries
-- ================================================================
-- Run these queries after setup to verify everything is created correctly:

-- Check tables are created
SHOW TABLES;

-- Check table structures
DESCRIBE integrations;
DESCRIBE toyyibpay_configs;
DESCRIBE transactions;

-- Check indexes
SHOW INDEX FROM integrations;
SHOW INDEX FROM toyyibpay_configs;
SHOW INDEX FROM transactions;

-- Check foreign key constraints
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- ================================================================
-- Setup Complete!
-- ================================================================
-- All tables, indexes, and constraints have been created.
-- Update your .env file with the correct database credentials.
-- ================================================================