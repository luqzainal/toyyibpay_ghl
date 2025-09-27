-- ================================================================
-- GHL ToyyibPay Integration - Database Schema with camelCase columns
-- ================================================================
--
-- This file creates the standardized camelCase column naming convention
-- Run this after the migration has been applied
-- ================================================================

-- Table: integrations with camelCase columns
CREATE TABLE IF NOT EXISTS integrations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

    -- GHL Integration identifiers
    locationId VARCHAR(255) NOT NULL UNIQUE COMMENT 'GHL Location ID',
    companyId VARCHAR(255) NOT NULL COMMENT 'GHL Company ID',

    -- OAuth tokens (encrypted)
    accessToken TEXT NOT NULL COMMENT 'GHL OAuth access token (encrypted)',
    refreshToken TEXT NOT NULL COMMENT 'GHL OAuth refresh token (encrypted)',

    -- Plugin-generated API key
    apiKey VARCHAR(255) NOT NULL UNIQUE COMMENT 'Unique API key for this location',

    -- Installation tracking
    installedAt TIMESTAMP NULL COMMENT 'When the plugin was installed',
    uninstalledAt TIMESTAMP NULL COMMENT 'When the plugin was uninstalled',

    -- Status tracking
    isActive BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Is the integration currently active',
    providerRegistered BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Is provider registered in GHL',

    -- Timestamps
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_locationId_isActive (locationId, isActive),
    INDEX idx_companyId (companyId),
    INDEX idx_apiKey (apiKey),
    INDEX idx_apiKey_isActive (apiKey, isActive)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: toyyibpay_configs with camelCase columns
CREATE TABLE IF NOT EXISTS toyyibpay_configs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

    -- Link to integration
    locationId VARCHAR(255) NOT NULL UNIQUE COMMENT 'GHL Location ID - links to integrations table',

    -- Production/Live environment credentials (encrypted)
    secretKeyLive TEXT NULL COMMENT 'ToyyibPay secret key for live environment (encrypted)',
    categoryCodeLive VARCHAR(255) NULL COMMENT 'ToyyibPay category code for live environment',

    -- Sandbox/Test environment credentials (encrypted)
    secretKeySandbox TEXT NULL COMMENT 'ToyyibPay secret key for sandbox environment (encrypted)',
    categoryCodeSandbox VARCHAR(255) NULL COMMENT 'ToyyibPay category code for sandbox environment',

    -- Active mode setting
    modeActive ENUM('sandbox', 'production') NOT NULL DEFAULT 'sandbox' COMMENT 'Currently active ToyyibPay mode',

    -- Configuration status
    isConfigured BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Is ToyyibPay properly configured for this location',
    configuredAt TIMESTAMP NULL COMMENT 'When ToyyibPay was first configured',

    -- Timestamps
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_locationId (locationId),
    INDEX idx_locationId_modeActive (locationId, modeActive),

    -- Foreign key constraint
    CONSTRAINT fk_toyyibpay_configs_locationId
        FOREIGN KEY (locationId)
        REFERENCES integrations(locationId)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: transactions with camelCase columns
CREATE TABLE IF NOT EXISTS transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

    -- Link to integration
    locationId VARCHAR(255) NOT NULL COMMENT 'GHL Location ID',

    -- GHL transaction identifiers
    ghlOrderId VARCHAR(255) NULL COMMENT 'GHL Order ID from payment request',
    ghlTransactionId VARCHAR(255) NULL COMMENT 'GHL Transaction ID for tracking',

    -- ToyyibPay transaction identifiers
    toyyibpayBillcode VARCHAR(255) NULL UNIQUE COMMENT 'ToyyibPay generated bill code',
    toyyibpayBillId VARCHAR(255) NULL COMMENT 'ToyyibPay bill ID',

    -- Transaction details
    amount DECIMAL(10,2) NOT NULL COMMENT 'Transaction amount in MYR',
    currency VARCHAR(3) NOT NULL DEFAULT 'MYR' COMMENT 'Transaction currency',
    description TEXT NULL COMMENT 'Payment description',

    -- Customer information
    customerName VARCHAR(255) NULL COMMENT 'Customer name',
    customerEmail VARCHAR(255) NULL COMMENT 'Customer email',
    customerPhone VARCHAR(255) NULL COMMENT 'Customer phone',

    -- Status tracking
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded')
           NOT NULL DEFAULT 'pending' COMMENT 'Transaction status',

    -- Environment tracking
    environment ENUM('sandbox', 'production') NOT NULL COMMENT 'Which environment was used',

    -- Webhook and callback tracking
    toyyibpayCallbackAt TIMESTAMP NULL COMMENT 'When ToyyibPay callback was received',
    ghlNotifiedAt TIMESTAMP NULL COMMENT 'When GHL was notified of status',

    -- JSON data for debugging
    toyyibpayRequestData JSON NULL COMMENT 'Original ToyyibPay request data',
    toyyibpayResponseData JSON NULL COMMENT 'ToyyibPay response data',
    ghlWebhookData JSON NULL COMMENT 'Data sent to GHL webhook',

    -- Timestamps
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_locationId (locationId),
    INDEX idx_ghlOrderId (ghlOrderId),
    INDEX idx_ghlTransactionId (ghlTransactionId),
    INDEX idx_toyyibpayBillcode (toyyibpayBillcode),
    INDEX idx_locationId_status (locationId, status),
    INDEX idx_status_createdAt (status, createdAt),
    INDEX idx_amount_status (amount, status),
    INDEX idx_customerEmail (customerEmail),
    INDEX idx_environment_status (environment, status),

    -- Foreign key constraint
    CONSTRAINT fk_transactions_locationId
        FOREIGN KEY (locationId)
        REFERENCES integrations(locationId)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Verification Queries for camelCase schema
-- ================================================================

-- Check tables exist
SHOW TABLES;

-- Check column structures
DESCRIBE integrations;
DESCRIBE toyyibpay_configs;
DESCRIBE transactions;

-- Sample queries with camelCase columns
SELECT locationId, companyId, isActive FROM integrations LIMIT 1;
SELECT locationId, modeActive, isConfigured FROM toyyibpay_configs LIMIT 1;
SELECT locationId, ghlOrderId, customerEmail, status FROM transactions LIMIT 1;