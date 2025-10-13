<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated and in the email whitelist
        if (! auth()->check() || ! in_array(auth()->user()->email, config('admin.email_whitelist'))) {
            // Redirect to the login page or show an error
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
