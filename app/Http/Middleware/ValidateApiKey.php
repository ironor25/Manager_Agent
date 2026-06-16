<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized. API Key is missing.'], 401);
        }

        $apiKey = \App\Models\ApiKey::where('api_key', $token)
            ->where('is_active', true)
            ->first();

        if (!$apiKey) {
            return response()->json(['message' => 'Unauthorized. Invalid or inactive API Key.'], 401);
        }

        // Update last used at
        $apiKey->update(['last_used_at' => now()]);

        // Optional: you can authenticate the user for the request if needed
        // Auth::login($apiKey->user);

        return $next($request);
    }
}
