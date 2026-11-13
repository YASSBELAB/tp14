<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogStructuredRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = hrtime(true);
        $response = $next($request);

        if ($request->path() !== 'metrics') {
            Log::info('HTTP request completed', [
                'event' => 'http_request',
                'method' => $request->method(),
                'path' => '/' . ltrim($request->path(), '/'),
                'ip' => $request->ip(),
                'status' => $response->getStatusCode(),
                'duration_ms' => (hrtime(true) - $startedAt) / 1_000_000,
            ]);
        }

        return $response;
    }
}
