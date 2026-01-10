<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;

class OllamaStreamController extends Controller
{
    public function chatStream(Request $request)
    {
        $baseUrl = rtrim(config('ollama.base_url'), '/');
        $model = $request->input('ollama_model', config('ollama.default_model'));
        $messages = $request->input('messages', []);
        if (!is_array($messages)) $messages = [];

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'stream' => true,
        ];
        $client = new Client(['base_uri' => $baseUrl, 'timeout' => (int) config('ollama.timeout', 60)]);

        $response = new StreamedResponse(function () use ($client, $payload) {
            $res = $client->post('/api/chat', ['json' => $payload, 'stream' => true]);
            $body = $res->getBody();
            while (!$body->eof()) {
                $chunk = $body->read(8192);
                if ($chunk === '') { usleep(50_000); continue; }
                foreach (preg_split("/\r?\n/", $chunk) as $line) {
                    if ($line === '') continue;
                    echo 'data: '.rtrim($line)."\n\n";
                }
                @ob_flush(); @flush();
            }
            echo "event: end\n";
            echo "data: [DONE]\n\n";
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');
        return $response;
    }
}
