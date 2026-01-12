<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\OllamaStreamController;
use App\Http\Controllers\ProxyController;

Route::post('/ollama/generate', [OllamaController::class, 'generate'])->middleware('ollama.inject');
Route::post('/ollama/chat/stream', [OllamaStreamController::class, 'chatStream']);

// Generic reverse proxy: /api/proxy/{target}/{path?}
// Example: /api/proxy/ollama/api/chat
Route::any('/proxy/{target}/{path?}', [ProxyController::class, 'proxy'])
	->where('path', '.*');
