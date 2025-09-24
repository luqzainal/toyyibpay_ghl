<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ToyyibPay API Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains settings for ToyyibPay payment gateway
    | integration including sandbox and production environment URLs and
    | default settings for the payment processing system.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Base URLs
    |--------------------------------------------------------------------------
    |
    | These are the base URLs for ToyyibPay API endpoints. The system will
    | switch between sandbox and production based on the active mode.
    |
    */

    'sandbox_url' => env('TOYYIBPAY_SANDBOX_URL', 'https://dev.toyyibpay.com'),
    'production_url' => env('TOYYIBPAY_PRODUCTION_URL', 'https://toyyibpay.com'),

    /*
    |--------------------------------------------------------------------------
    | Default Mode
    |--------------------------------------------------------------------------
    |
    | The default mode for ToyyibPay integration. This can be overridden
    | per location in the database configuration.
    | 
    | Supported modes: "sandbox", "production"
    |
    */

    'default_mode' => env('TOYYIBPAY_DEFAULT_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    |
    | These are the specific API endpoints used for various ToyyibPay
    | operations like creating bills, checking status, etc.
    |
    */

    'endpoints' => [
        'create_bill' => '/index.php/api/createBill',
        'bill_status' => '/index.php/api/getBillTransactions',
        'get_bill' => '/index.php/api/getBill',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for bill creation and payment processing.
    |
    */

    'defaults' => [
        'charge_tax' => 1, // 1 = include tax, 0 = exclude tax
        'bill_description' => 'Payment via GHL Integration',
        'bill_return_url' => null, // Will be set dynamically
        'bill_callback_url' => null, // Will be set dynamically
        'bill_external_reference_no' => null, // Will be set from GHL order ID
        'bill_to' => '', // Customer email
        'bill_email' => '', // Customer email
        'bill_phone' => '', // Customer phone
        'bill_name' => '', // Customer name
        'bill_address' => '', // Customer address
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout Settings
    |--------------------------------------------------------------------------
    |
    | API request timeout settings in seconds.
    |
    */

    'timeout' => [
        'connection' => 30,
        'request' => 60,
    ],

];
