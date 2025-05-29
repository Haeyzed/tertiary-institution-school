<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Frontend URLs
    |--------------------------------------------------------------------------
    |
    | These URLs are used for redirecting users to the frontend application
    | for password reset and email verification.
    |
    */

    'frontend' => [
        'url' => env('FRONTEND_URL', 'http://localhost:3000'),

        'password_reset' => [
            'url' => env('FRONTEND_PASSWORD_RESET_URL', '/auth/reset-password'),
            'parameters' => ['token', 'email', 'user_type'],
        ],

        'email_verification' => [
            'url' => env('FRONTEND_EMAIL_VERIFICATION_URL', '/auth/verify-email'),
            'parameters' => ['id', 'hash', 'user_type'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API URLs
    |--------------------------------------------------------------------------
    |
    | These URLs are used for API endpoints that handle password reset
    | and email verification.
    |
    */

    'api' => [
        'password_reset' => [
            'url' => env('API_PASSWORD_RESET_URL', '/api/v1/auth/reset-password'),
        ],

        'email_verification' => [
            'url' => env('API_EMAIL_VERIFICATION_URL', '/api/v1/auth/verify-email'),
        ],
    ],

];
