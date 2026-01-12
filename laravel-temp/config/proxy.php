<?php

return [
    // Master switch
    'enabled' => env('PROXY_ENABLED', true),

    // Default upstream key to use when {target} is omitted
    'default' => env('PROXY_DEFAULT', 'ollama'),

    // Map of targets to upstream base URIs
    'upstreams' => [
        // e.g., /api/proxy/ollama/* → http://127.0.0.1:11434/*
        'ollama' => env('PROXY_OLLAMA_BASE', 'http://127.0.0.1:11434'),
        // add more, e.g. 'internal' => env('PROXY_INTERNAL_BASE', 'https://internal.example.com')
    ],

    // Only allow proxying to these hosts (defaults to hosts from 'upstreams' if empty)
    'allowed_hosts' => [
        // '127.0.0.1', 'internal.example.com'
    ],

    // Whether to allow usage outside local environment (default: false)
    'allow_public' => env('PROXY_ALLOW_PUBLIC', false),

    // Request options
    'timeout' => (int) env('PROXY_TIMEOUT', 30),

    // Headers to forward from the incoming request (lowercase names)
    // Leave empty to forward a safe minimal subset only
    'forward_headers' => [
        'accept',
        'content-type',
        'authorization',
        'user-agent',
    ],
];
