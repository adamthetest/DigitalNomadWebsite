<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Headers Middleware
 *
 * Adds essential security headers to HTTP responses to protect against
 * common web vulnerabilities like XSS, clickjacking, and MIME sniffing.
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy - control referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy - prevent XSS and code injection
        $csp = "default-src 'self'; ".
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com; ".
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.jsdelivr.net https://unpkg.com; ".
               "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net; ".
               "img-src 'self' data: https: https://*.tile.openstreetmap.org https://*.tile.osm.org; ".
               "connect-src 'self' ws: wss: https://*.tile.openstreetmap.org https://*.tile.osm.org; ".
               "frame-ancestors 'none';";
        $response->headers->set('Content-Security-Policy', $csp);

        // Permissions Policy - control browser features
        $permissionsPolicy = 'geolocation=(), '.
                           'microphone=(), '.
                           'camera=(), '.
                           'payment=(), '.
                           'usb=(), '.
                           'magnetometer=(), '.
                           'gyroscope=(), '.
                           'speaker=()';
        $response->headers->set('Permissions-Policy', $permissionsPolicy);

        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        // HSTS (HTTP Strict Transport Security) - only for HTTPS
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }
        
        // Cross-Origin-Opener-Policy - prevent cross-origin attacks
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        return $response;
    }
}
