<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogAccountDeletionAttempts
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log all attempts to access the data privacy page for security monitoring
        if ($request->route() && str_contains($request->route()->getName() ?? '', 'filament.admin.pages.data-privacy')) {
            Log::info('Data Privacy page accessed', [
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
                'referer' => $request->header('referer'),
            ]);
        }

        return $next($request);
    }
}
