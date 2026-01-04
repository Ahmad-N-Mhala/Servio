<?php

return [
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'), // twilio, unifonic, sms_ae, log
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM'), // Alphanumeric Sender ID or Phone Number
    ],

    'unifonic' => [
        'api_key' => env('UNIFONIC_API_KEY'),
        'sender_id' => env('UNIFONIC_SENDER_ID'),
    ],

    'sms_ae' => [
        'username' => env('SMS_AE_USERNAME'),
        'password' => env('SMS_AE_PASSWORD'),
        'sender_id' => env('SMS_AE_SENDER_ID'),
    ],
];

