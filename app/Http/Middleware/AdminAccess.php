<?php

namespace App\Http\Middleware;

use App\Models\SecurityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        // Check if user is admin (you can customize this logic)
        $user = auth()->user();

        // Option 1: Check by email domain or specific admin emails
        $adminEmails = ['admin@digitalnomad.com'];
        if (! in_array($user->email, $adminEmails)) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Log admin access
        SecurityLog::logAdminAccess($user, $request->ip());

        return $next($request);
    }
}
