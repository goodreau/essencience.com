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
        /** @var Response $response */
        $response = $next($request);

        $csp = implode('; ', [
            // Baseline policy
            "default-src 'self'",

            // Scripts: disallow external execution beyond self; no inline/nonce usage
            "script-src 'self'",

            // Styles: allow Bunny fonts CSS
            "style-src 'self' https://fonts.bunny.net",

            // Fonts: Bunny + data URIs
            "font-src 'self' https://fonts.bunny.net data:",

            // Images: self + data URIs
            "img-src 'self' data:",

            // AJAX / server requests
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
