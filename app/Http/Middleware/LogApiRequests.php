<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Log;

class LogApiRequests extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [];

    /**
     * Handle an incoming request.
     */
    public function handle($request, $next)
    {
        // Log incoming request
        if ($request->is('api/*')) {
            Log::channel('api')->info('API Request', [
                'method' => $request->method(),
                'path' => $request->path(),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        $response = $next($request);

        // Log response
        if ($request->is('api/*')) {
            Log::channel('api')->info('API Response', [
                'method' => $request->method(),
                'path' => $request->path(),
                'status' => $response->status(),
                'user_id' => auth()->id(),
            ]);
        }

        return $response;
    }
}
