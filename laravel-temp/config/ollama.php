<?php

return [
    'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
    'default_model' => env('OLLAMA_MODEL', 'llama3.1'),
    'timeout' => (int) env('OLLAMA_TIMEOUT', 60),
];
