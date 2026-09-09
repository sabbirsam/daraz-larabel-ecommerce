<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSLCommerz Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials, API endpoints, and sandbox mode settings for SSLCommerz
    | Bangladesh Payment Gateway.
    |
    */
    'store_id' => env('SSLCZ_STORE_ID', 'testbox'),
    'store_password' => env('SSLCZ_STORE_PASSWORD', 'qwerty'),
    'sandbox' => env('SSLCZ_SANDBOX', true),
    'api_domain' => env('SSLCZ_SANDBOX', true) 
        ? 'https://sandbox.sslcommerz.com' 
        : 'https://securepay.sslcommerz.com',
    'init_url' => '/gwprocess/v4/api.php',
    'validate_url' => '/validator/api/validationserverAPI.php',
    'currency' => 'BDT',
];
