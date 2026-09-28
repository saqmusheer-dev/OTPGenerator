<?php

return [
    'app' => [
        'name' => 'OTPGenerator',
        'base_url' => 'https://your-domain.com',
        'timezone' => 'Asia/Kolkata',
    ],
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'otp_generator',
        'user' => 'root',
        'password' => '',
    ],
    'otp' => [
        'length' => 6,
        'expiry_seconds' => 300,
        'max_attempts' => 5,
        'resend_cooldown_seconds' => 30,
        'daily_limit_default' => 1000,
    ],
];
