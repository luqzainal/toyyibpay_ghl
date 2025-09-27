-- ================================================================
-- GHL ToyyibPay Integration - InfinityFree Database Setup
-- ================================================================
--
-- This SQL file creates all necessary tables for InfinityFree hosting
-- including Laravel system tables that are required.
--
-- Run this file in your InfinityFree MySQL database through phpMyAdmin
--
-- Database: if0_XXXXXXXX_toyyibpayghl (your InfinityFree database name)
-- ================================================================

-- ================================================================
-- Laravel System Tables - Required for Laravel to work properly
-- ================================================================

-- Sessions table (required since SESSION_DRIVER=database)
DROP TABLE IF EXISTS sessions;

CREATE TABLE sessions (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cache tables (required since CACHE_STORE=database)
DROP TABLE IF EXISTS cache;

CREATE TABLE cache (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS cache_locks;

CREATE TABLE cache_locks (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jobs tables (required since QUEUE_CONNECTION=database)
DROP TABLE IF EXISTS jobs;

CREATE TABLE jobs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX jobs_queue_index (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS job_batches;

CREATE TABLE job_batches (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS failed_jobs;

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Application Tables - GHL ToyyibPay Integration
-- ================================================================

-- Table: integrations
-- Stores GHL OAuth integration data and plugin API keys
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
    INDEX idx_api_key (api_key),
    INDEX idx_api_key_active (api_key, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='GHL OAuth integrations and API keys';

-- Table: toyyibpay_configs
-- Stores ToyyibPay configuration for each GHL location
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

    -- Foreign key constraint (Note: InfinityFree may have limitations with foreign keys)
    CONSTRAINT fk_toyyibpay_configs_location
        FOREIGN KEY (location_id)
        REFERENCES integrations(location_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ToyyibPay configuration per GHL location';

-- Table: transactions
-- Stores all payment transactions between GHL and ToyyibPay
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

    -- JSON data for debugging (using TEXT for broader MySQL compatibility)
    toyyibpay_request_data TEXT NULL COMMENT 'Original ToyyibPay request data (JSON)',
    toyyibpay_response_data TEXT NULL COMMENT 'ToyyibPay response data (JSON)',
    ghl_webhook_data TEXT NULL COMMENT 'Data sent to GHL webhook (JSON)',

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
    INDEX idx_amount_status (amount, status),
    INDEX idx_customer_email (customer_email),
    INDEX idx_environment_status (environment, status),

    -- Foreign key constraint (Note: InfinityFree may have limitations with foreign keys)
    CONSTRAINT fk_transactions_location
        FOREIGN KEY (location_id)
        REFERENCES integrations(location_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payment transactions between GHL and ToyyibPay';

-- ================================================================
-- InfinityFree Specific Notes
-- ================================================================
/*
1. InfinityFree has some limitations:
   - Limited MySQL resources
   - May not support all foreign key constraints
   - Limited database size (typically 400MB)
   - May have restrictions on certain MySQL features

2. If you encounter foreign key errors, you can remove the CONSTRAINT lines
   and rely on application-level data integrity instead.

3. Make sure your .env file is configured properly for InfinityFree:
   - Use the correct database host provided by InfinityFree
   - Use your InfinityFree MySQL username/password
   - Use your assigned database name (typically starts with if0_)

4. For sessions, you might want to consider using file-based sessions instead:
   - Change SESSION_DRIVER=file in .env
   - This would reduce database load on InfinityFree
*/

-- ================================================================
-- Verification Queries
-- ================================================================
-- Run these after setup to verify everything is working:

-- Check all tables are created
SHOW TABLES;

-- Check table structures
DESCRIBE integrations;
DESCRIBE toyyibpay_configs;
DESCRIBE transactions;
DESCRIBE sessions;

-- Check if any data exists
SELECT COUNT(*) as integration_count FROM integrations;
SELECT COUNT(*) as config_count FROM toyyibpay_configs;
SELECT COUNT(*) as transaction_count FROM transactions;

-- ================================================================
-- InfinityFree Setup Complete!
-- ================================================================
-- All tables have been created and optimized for InfinityFree hosting.
--
-- Next steps:
-- 1. Update your .env file with InfinityFree database credentials
-- 2. Upload your Laravel files to htdocs
-- 3. Test the installation
-- ================================================================