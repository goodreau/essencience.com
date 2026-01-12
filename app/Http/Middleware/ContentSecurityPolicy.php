<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));

        \View::share('cspNonce', $nonce);

        /** @var Response $response */
        $response = $next($request);

        $csp = implode('; ', [
            // Default
            "default-src 'self'",
            // Scripts: self, Tailwind CDN, and nonce for inline scripts used by Flux/Tailwind config
            "script-src 'self' https://cdn.tailwindcss.com 'nonce-{$nonce}'",
            // Styles: self, Bunny fonts CSS, and nonce for inline <style> injected by Flux
            "style-src 'self' https://fonts.bunny.net 'nonce-{$nonce}'",
            // Fonts: self + Bunny fonts + data URIs
            "font-src 'self' https://fonts.bunny.net data:",
            // Images: self + data URIs
            "img-src 'self' data:",
            // Connections for Livewire/AJAX
            "connect-src 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp, false);

        return $response;
    }
}
