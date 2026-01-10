# Laravel Reverse Proxy

A minimal reverse proxy endpoint powered by Guzzle, suitable for local development or tightly controlled environments.

## Setup

1. Require Guzzle if not already installed:
   ```bash
   composer require guzzlehttp/guzzle
   ```
2. Configure upstreams in `config/proxy.php`:
   ```php
   return [
     'enabled' => true,
     'default' => 'ollama',
     'upstreams' => [
       'ollama' => env('PROXY_OLLAMA_BASE', 'http://127.0.0.1:11434'),
     ],
     'allow_public' => env('PROXY_ALLOW_PUBLIC', false),
     'timeout' => (int) env('PROXY_TIMEOUT', 30),
   ];
   ```

By default the proxy is restricted to the `local` environment unless `PROXY_ALLOW_PUBLIC=true` is set.

## Routes

- `ANY /api/proxy/{target}/{path?}`
  - `{target}` maps to an upstream key in `config/proxy.php`.
  - `{path}` is appended to the upstream base URI.

Examples:

```bash
# GET proxied request
curl -i "http://localhost:8000/api/proxy/ollama/" \
  -H 'Accept: application/json'

# POST JSON (SSE/chat compatible; -N to stream)
curl -N -i "http://localhost:8000/api/proxy/ollama/api/chat" \
  -H 'Content-Type: application/json' \
  -d '{"model":"llama3","messages":[{"role":"user","content":"Hello"}]}'

# Forward query params
curl -i "http://localhost:8000/api/proxy/ollama/api/tags?limit=10"
```

## Notes

- The controller streams responses back to the client, enabling SSE and large downloads.
- Only a safe subset of request headers is forwarded by default; customize via `proxy.forward_headers`.
- File uploads are supported via `multipart/form-data`.
- Security: keep `allow_public=false` unless you understand the risks. Consider adding auth/throttling middleware before exposing broadly.
