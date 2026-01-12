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
            // Baseline policy
            "default-src 'self'",

            // Scripts: self + Tailwind CDN + nonce for Flux and config inline scripts
            "script-src 'self' https://cdn.tailwindcss.com 'nonce-{$nonce}'",

            // Styles: allow Bunny fonts CSS and inline styles (Tailwind CDN injects <style> tags without nonce)
            "style-src 'self' https://fonts.bunny.net 'nonce-{$nonce}' 'unsafe-inline'",

            // Fonts: Bunny + data URIs
            "font-src 'self' https://fonts.bunny.net data:",

            // Images: self + data URIs
            "img-src 'self' data:",

            // AJAX / Livewire requests
            "connect-src 'self'",

            // Disallow legacy embeddables
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "upgrade-insecure-requests",
        ]);

        $response->headers->set('Content-Security-Policy', $csp, false);

        return $response;
    }
}
