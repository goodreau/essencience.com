<?php

namespace App\Http\Middleware;

use App\Services\OllamaClient;
use Closure;
use Illuminate\Http\Request;

/**
 * OllamaMiddleware
 *
 * If a request carries an `ollama_prompt` (query or JSON body),
 * call Ollama and attach the result to request attributes as `ollama.response`.
 * If `ollama_autorespond` is truthy, return JSON immediately instead of passing to controller.
 */
class OllamaMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $prompt = $request->input('ollama_prompt');
        $auto = filter_var($request->input('ollama_autorespond'), FILTER_VALIDATE_BOOLEAN);

        if ($prompt !== null && $prompt !== '') {
            $client = app(OllamaClient::class);
            try {
                $result = $client->generate($prompt, [
                    'model' => $request->input('ollama_model'),
                    'options' => $request->input('ollama_options', []),
                ]);
                $request->attributes->set('ollama.response', $result);

                if ($auto) {
                    return response()->json(['ok' => true, 'ollama' => $result]);
                }
            } catch (\Throwable $e) {
                if ($auto) {
                    return response()->json(['ok' => false, 'error' => $e->getMessage()], 502);
                }
                // else continue to controller with error attached
                $request->attributes->set('ollama.error', $e->getMessage());
            }
        }

        return $next($request);
    }
}
