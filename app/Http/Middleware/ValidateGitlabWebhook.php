<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateGitlabWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Gitlab-Token');
        $secret = env('GITLAB_WEBHOOK_SECRET');

        // Only enforce the secret token if it is actually set in the .env file
        if ($secret && $token !== $secret) {
            \Illuminate\Support\Facades\Log::warning('GitLab Webhook unauthorized attempt.', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
