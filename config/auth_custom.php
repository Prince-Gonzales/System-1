<?php

return [
    'require_verification' => env('AUTH_REQUIRE_VERIFICATION', false),
    'idle_timeout_seconds' => env('AUTH_IDLE_TIMEOUT', 900),
    'remember_days' => env('AUTH_REMEMBER_DAYS', 30),
    'code_length' => env('AUTH_CODE_LENGTH', 6),
    'verification' => [
        'max_attempts' => env('AUTH_MAX_ATTEMPTS', 5),
        'wait_times' => [1, 2, 3, 4, 5], // minutes per attempt
    ],
    'email_cipher' => [
        'key' => env('AUTH_EMAIL_KEY', 'a3f1d5c9b7e8a2f4c6d9e1b8a4c7e3f2'),
        'hmac_key' => env('AUTH_EMAIL_HMAC_KEY', 'd8e7c5f2b3a4e9d1c6f8b7a2d5e3c4f1'),
    ],
];

