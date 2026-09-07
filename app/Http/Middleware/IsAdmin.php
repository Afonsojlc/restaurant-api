<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle incoming request and verify administrator / restaurant owner role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Deny access with 403 Forbidden if user is not an administrator
        if (!$request->user()?->isAdmin()) {
            return response()->json(['message' => 'Access forbidden: Administrator privileges required.'], 403);
        }

        return $next($request);
    }
}
