<?php

namespace App\Http\Middleware;

use App\Models\BannedIp;
use App\Models\SecurityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckBannedIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip banned IP check in testing environment
        if (app()->environment('testing')) {
            return $next($request);
        }

        $ipAddress = $request->ip();

        try {
            // Check if IP is banned
            if (BannedIp::isBanned($ipAddress)) {
                // Log the banned access attempt
                SecurityLog::logBannedAccess($ipAddress);

                // Return 403 Forbidden
                return response()->view('errors.403', [
                    'message' => 'Your IP address has been banned from accessing this website.',
                    'title' => 'Access Denied',
                ], 403);
            }
        } catch (\Exception $e) {
            // If database is not available, continue without banning check
            // This prevents issues during testing or when database is temporarily unavailable
            if (! app()->environment('testing')) {
                \Log::warning('Banned IP check failed: '.$e->getMessage());
            }
        }

        return $next($request);
    }
}
