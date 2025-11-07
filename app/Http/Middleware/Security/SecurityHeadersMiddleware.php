<?php

namespace App\Http\Middleware\Security;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Security Policy (CSP) - Protect against XSS attacks
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://js.pusher.com https://www.google.com https://www.gstatic.com",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.bunny.net https://cdnjs.cloudflare.com data:",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' https://js.pusher.com https://*.pusher.com https://*.pusher.cloud wss://*.pusher.com wss://*.pusher.cloud https://www.google.com",
            "frame-src 'self' https://www.google.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'", // Prevent clickjacking
            "upgrade-insecure-requests",
        ];
        
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        // X-Frame-Options - Additional clickjacking protection
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // X-Content-Type-Options - Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer-Policy - Control referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy - Control browser features
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Cross-Origin-Opener-Policy (COOP) - Origin isolation
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        // Cross-Origin-Embedder-Policy (COEP) - Additional isolation
        // Set to 'unsafe-none' to avoid breaking third-party integrations
        // Can be changed to 'require-corp' if all resources support CORS
        $response->headers->set('Cross-Origin-Embedder-Policy', 'unsafe-none');

        // Cross-Origin-Resource-Policy (CORP) - Resource isolation
        // Set to 'cross-origin' to allow third-party resources (CDN, fonts, etc.)
        $response->headers->set('Cross-Origin-Resource-Policy', 'cross-origin');

        // HSTS (HTTP Strict Transport Security) - Force HTTPS
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}



