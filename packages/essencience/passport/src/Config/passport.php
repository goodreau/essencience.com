<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Certificate-Based Passport Configuration
    |--------------------------------------------------------------------------
    |
    | Configure certificate-based authentication for your Laravel application.
    | Users authenticate using X.509 certificates instead of passwords.
    |
    */

    'enabled' => env('PASSPORT_ENABLED', true),

    // Certificate validation
    'verify_ca' => env('PASSPORT_VERIFY_CA', true),

    'ca_cert_path' => env('PASSPORT_CA_CERT', storage_path('ca/ca-cert.pem')),

    // Client certificate paths (for testing/development)
    'client_cert_header' => env('PASSPORT_CERT_HEADER', 'SSL_CLIENT_CERT'),
    'client_serial_header' => env('PASSPORT_SERIAL_HEADER', 'SSL_CLIENT_SERIAL'),
    'client_dn_header' => env('PASSPORT_DN_HEADER', 'SSL_CLIENT_S_DN'),

    // Certificate validity
    'certificate_validity_days' => env('PASSPORT_CERT_VALIDITY_DAYS', 365),

    // User certificate storage
    'user_certs_path' => env('PASSPORT_USER_CERTS_PATH', storage_path('passport/users')),

    // Automatically create users from certificates
    'auto_create_users' => env('PASSPORT_AUTO_CREATE_USERS', false),

    // User model
    'user_model' => env('PASSPORT_USER_MODEL', \App\Models\User::class),

    // Certificate fields mapping to user attributes
    'certificate_mapping' => [
        'email' => 'emailAddress',  // Email from certificate
        'name' => 'CN',             // Common Name from certificate
        'serial' => 'serialNumber', // Certificate serial number
    ],
];
