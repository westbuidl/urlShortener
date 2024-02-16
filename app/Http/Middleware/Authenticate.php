<?php

namespace App\Http\Middleware;

use session;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AuthenticateSession //extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
   /* protected function redirectTo(Request $request): ?string
    {

        return $request->expectsJson() ? null : route('login');

    };*/
    public function handle(Request $request, Closure $next)
    {
        // Check if user ID exists in the session
        if (session()->has('userID')) {
            // User is authenticated, proceed to the next request
            return $next($request);
        }

        // User is not authenticated, redirect to login or return error response
        return response()->json([
            'message' => 'Unauthorized. Please login.',
        ], 401);
    }

    
}
