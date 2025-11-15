<?php 
return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // If you want to allow ALL domains:
    // 'allowed_origins' => ['*'],

    // If you want to allow only specific frontends:
    'allowed_origins' => [
        'https://attendance-gold-one.vercel.app',
        'https://attendance.xetroot.com',
        'http://localhost:5173',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];

