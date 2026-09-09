<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default SMS Gateway Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "log", "twilio", "bdsms"
    |
    */
    'default' => env('SMS_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | SMS Drivers Configuration
    |--------------------------------------------------------------------------
    */
    'drivers' => [
        'log' => [
            'channel' => env('SMS_LOG_CHANNEL', 'single'),
        ],

        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],

        'bdsms' => [
            'api_key' => env('BDSMS_API_KEY'),
            'sender_id' => env('BDSMS_SENDER_ID', 'DARAZBD'),
            'endpoint' => env('BDSMS_ENDPOINT', 'https://api.smsnet24.com/send-sms'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Code Settings
    |--------------------------------------------------------------------------
    */
    'otp' => [
        'length' => 6,
        'expiry_minutes' => 5,
        'max_attempts' => 5,
        'resend_cooldown_seconds' => 60,
    ],
];
