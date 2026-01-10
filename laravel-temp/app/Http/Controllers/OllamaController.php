<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OllamaController extends Controller
{
    public function generate(Request $request)
    {
        $res = $request->attributes->get('ollama.response');
        $err = $request->attributes->get('ollama.error');
        if ($err) {
            return response()->json(['ok' => false, 'error' => $err], 502);
        }
        return response()->json(['ok' => true, 'ollama' => $res]);
    }
}
