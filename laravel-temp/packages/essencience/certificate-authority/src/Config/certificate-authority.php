<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Certificate Authority Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Certificate Authority settings for issuing and managing
    | SSL/TLS certificates using macOS Keychain.
    |
    */

    'domain' => env('CA_DOMAIN', 'essencience.com'),

    'ca_name' => env('CA_NAME', 'Essencience Root CA'),

    'paths' => [
        'ca_key' => env('CA_KEY_PATH', storage_path('ca/ca-key.pem')),
        'ca_cert' => env('CA_CERT_PATH', storage_path('ca/ca-cert.pem')),
        'server_key' => env('SERVER_KEY_PATH', storage_path('ca/server-key.pem')),
        'server_cert' => env('SERVER_CERT_PATH', storage_path('ca/server-cert.pem')),
    ],

    'validity' => [
        'ca' => env('CA_VALIDITY_DAYS', 3650), // 10 years
        'server' => env('SERVER_VALIDITY_DAYS', 365), // 1 year
    ],

    'keychain' => [
        'use_keychain' => env('USE_KEYCHAIN', true),
        'keychain_path' => env('KEYCHAIN_PATH', '~/Library/Keychains/login.keychain-db'),
        'system_keychain' => env('SYSTEM_KEYCHAIN', '/Library/Keychains/System.keychain'),
    ],

    'subject' => [
        'country' => env('CA_COUNTRY', 'US'),
        'state' => env('CA_STATE', 'State'),
        'locality' => env('CA_LOCALITY', 'City'),
        'organization' => env('CA_ORGANIZATION', 'Essencience'),
    ],
];
