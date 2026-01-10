<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OllamaClient
{
    protected Client $http;
    protected string $baseUrl;
    protected string $defaultModel;
    protected int $timeout;

    public function __construct(?Client $http = null)
    {
        $this->baseUrl = rtrim(config('ollama.base_url'), '/');
        $this->defaultModel = config('ollama.default_model');
        $this->timeout = (int) config('ollama.timeout', 60);
        $this->http = $http ?: new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
        ]);
    }

    /**
     * Simple text generation endpoint using /api/generate
     * @param string $prompt
     * @param array $options (model, stream=false, options: temperature, top_p, etc.)
     * @return array{model:string, response:string, done:bool}
     * @throws GuzzleException
     */
    public function generate(string $prompt, array $options = []): array
    {
        $model = $options['model'] ?? $this->defaultModel;
        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
        ];
        if (isset($options['options']) && is_array($options['options'])) {
            $payload['options'] = $options['options'];
        }

        $resp = $this->http->post('/api/generate', [
            'json' => $payload,
        ]);

        return json_decode((string) $resp->getBody(), true) ?? [];
    }

    /**
     * Chat endpoint using /api/chat with messages
     * @param array $messages [{role:"user|assistant|system", content:"..."}, ...]
     * @param array $options (model, stream=false)
     * @return array
     * @throws GuzzleException
     */
    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? $this->defaultModel;
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'stream' => false,
        ];
        $resp = $this->http->post('/api/chat', [ 'json' => $payload ]);
        return json_decode((string) $resp->getBody(), true) ?? [];
    }
}
