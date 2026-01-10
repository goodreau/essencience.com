<?php

use Illuminate\Support\Facades\Route;
use App\Services\Modules\ModuleRepository;
use App\Http\Controllers\ModulesController;
// use App\Livewire\AiChatbox;
use App\Services\OllamaClient;
use App\Services\RagService;

Route::get('/', function () {
    return view('welcome');
});

// AI Chatbox Component (disabled - Livewire not installed)
// Route::get('/ai-chat', AiChatbox::class)->name('ai.chat');

// API endpoint for testing Ollama
Route::get('/api/ai/test', function () {
    try {
        $ollama = app(OllamaClient::class);

        $result = $ollama->chat([
            [
                'role' => 'user',
                'content' => 'You are a helpful assistant. Respond with exactly 2 sentences about Laravel.',
            ]
        ]);

        return response()->json([
            'success' => true,
            'response' => $result['message']['content'] ?? 'No response',
            'model' => $result['model'] ?? 'unknown',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});

// Health check endpoint
Route::get('/api/health', function () {
    $health = [
        'app' => 'running',
        'ollama' => 'unknown',
        'qdrant' => 'unknown',
        'redis' => 'unknown',
    ];

    // Check Ollama
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(2)
            ->get(config('ollama.base_url') . '/api/tags');
        $health['ollama'] = $response->ok() ? 'running' : 'error';
    } catch (\Exception) {
        $health['ollama'] = 'unavailable';
    }

    // Check Qdrant
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(2)
            ->get('http://' . config('services.qdrant.host', 'localhost') . ':' . config('services.qdrant.port', 6333) . '/health');
        $health['qdrant'] = $response->ok() ? 'running' : 'error';
    } catch (\Exception) {
        $health['qdrant'] = 'unavailable';
    }

    // Check Redis
    try {
        $redis = \Illuminate\Support\Facades\Redis::connection();
        $redis->ping();
        $health['redis'] = 'running';
    } catch (\Exception) {
        $health['redis'] = 'unavailable';
    }

    return response()->json($health);
});

Route::get('/modules', function (ModuleRepository $repo) {
    $mods = $repo->discover();
    $html = '<h1>Modules</h1><ul>';
    foreach ($mods as $m) {
        $html .= '<li>'.htmlspecialchars($m['name']).' — '.($m['enabled']?'enabled':'disabled').'</li>';
    }
    $html .= '</ul>';
    return response($html);
});

Route::middleware(['ensure.local'])->prefix('admin/modules')->group(function () {
    Route::get('/', [ModulesController::class, 'index']);
    Route::post('/enable', [ModulesController::class, 'enable']);
    Route::post('/disable', [ModulesController::class, 'disable']);
    Route::post('/install', [ModulesController::class, 'install']);
});
