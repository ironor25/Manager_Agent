<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // If no specific roles required, or user has one of the roles
        if (empty($roles) || in_array($user->role, $roles)) {
            return $next($request);
        }

        // Redirect to a safe page if unauthorized
        return redirect('/')->with('error', 'You do not have permission to access this page.');
    }
}
