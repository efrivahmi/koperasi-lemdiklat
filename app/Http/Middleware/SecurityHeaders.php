<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and add security headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Determine environment-specific CSP
        $isDevelopment = app()->environment('local', 'development');
        $viteUrl = $isDevelopment ? 'https://koperasi-lemdiklat.test:5173 https://koperasi-lemdiklat.test:5174 https://localhost:5173 https://localhost:5174 ws: wss:' : '';

        // Content Security Policy (CSP)
        $csp = "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' {$viteUrl} https://cdn.jsdelivr.net; " .
            "script-src-elem 'self' 'unsafe-inline' {$viteUrl} https://cdn.jsdelivr.net; " .
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com; " .
            "style-src-elem 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com; " .
            "img-src 'self' data: blob: https:; " .
            "font-src 'self' data: https://fonts.bunny.net https://fonts.gstatic.com; " .
            "connect-src 'self' {$viteUrl}; " .
            "frame-ancestors 'none';";

        $response->headers->set('Content-Security-Policy', $csp);

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (formerly Feature Policy)
        $response->headers->set('Permissions-Policy',
            'geolocation=(), ' .
            'microphone=(), ' .
            'camera=(), ' .
            'payment=()'
        );

        // X-Frame-Options (defense against clickjacking)
        $response->headers->set('X-Frame-Options', 'DENY');

        // Remove X-Powered-By header (Laravel doesn't set this by default, but good to ensure)
        $response->headers->remove('X-Powered-By');

        // Ensure charset is set
        if ($response->headers->get('Content-Type') &&
            strpos($response->headers->get('Content-Type'), 'text/html') !== false) {
            $contentType = $response->headers->get('Content-Type');
            if (strpos($contentType, 'charset') === false) {
                $response->headers->set('Content-Type', $contentType . '; charset=utf-8');
            }
        }

        return $response;
    }
}
