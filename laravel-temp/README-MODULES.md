# Module Manager and Ollama Middleware

This app includes:

- Ollama middleware and client for calling a local Ollama server.
- A lightweight module manager that can auto-discover and register standalone Laravel-package modules stored in `modules/`.

## Ollama

Config: `config/ollama.php`

Env variables:

```
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=llama3.1
OLLAMA_TIMEOUT=60
```

Routes:

- `POST /api/ollama/generate` with body `{ "ollama_prompt": "Hello" }`
  - Middleware `ollama.inject` will call Ollama and the controller returns JSON

Client service: `App\Services\OllamaClient`

Dependency: `guzzlehttp/guzzle`. Install via:

```bash
composer update guzzlehttp/guzzle
```

## Module Manager

Config: `config/modules.php`

- `path`: folder where modules live (`modules/`)
- `enabled`: list of module names (e.g., `acme/hello`) to force-enable
- `auto_discover`: if true, providers are registered even if not listed in `enabled`

Provider: `App\Providers\ModuleServiceProvider` (registered in `bootstrap/app.php`)

Discovery: Reads each `composer.json` in `modules/*/*` and registers providers listed under `extra.laravel.providers`.

Example module added: `modules/acme/hello`

- Provider adds a route: `/hello-module`

### Artisan Commands

```bash
# List discovered modules
php artisan module:list

# Enable a module (adds to config/modules.php)
php artisan module:enable acme/hello

# Disable a module
php artisan module:disable acme/hello
```

### Simple UI

Visit `/modules` to see a quick list of modules and their status.

Admin UI (local only):

- `/admin/modules` lists discovered modules with enable/disable and Install-by-Git form.
- Protected by `ensure.local` middleware (only available when `APP_ENV=local`).

## Streaming Chat (Ollama)

Endpoint: `POST /api/ollama/chat/stream` (Server-Sent Events)

Body example:

```
{
  "messages": [
    {"role":"system","content":"You are helpful."},
    {"role":"user","content":"Stream a quick haiku."}
  ],
  "ollama_model": "llama3.1"
}
```

Client example (browser):

```js
const es = new EventSource('/api/ollama/chat/stream'); // if you proxy POST-to-GET with a token
// Or use fetch + ReadableStream for POST; EventSource only supports GET.
```

Note: This implementation uses SSE framing (lines as `data: {...}`), proxied from Ollama stream.
