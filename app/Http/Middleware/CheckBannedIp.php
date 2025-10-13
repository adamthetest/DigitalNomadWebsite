<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\BannedIp;
use App\Models\SecurityLog;

class CheckBannedIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ipAddress = $request->ip();
        
        // Check if IP is banned
        if (BannedIp::isBanned($ipAddress)) {
            // Log the banned access attempt
            SecurityLog::logBannedAccess($ipAddress);
            
            // Return 403 Forbidden
            return response()->view('errors.403', [
                'message' => 'Your IP address has been banned from accessing this website.',
                'title' => 'Access Denied'
            ], 403);
        }

        return $next($request);
    }
}