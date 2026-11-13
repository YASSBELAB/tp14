<?php

namespace App\Http\Middleware;

use App\Services\PrometheusMetricsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordPrometheusMetrics
{
    public function __construct(private readonly PrometheusMetricsService $metrics)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('metrics')) {
            return $next($request);
        }

        $startedAt = hrtime(true);
        $response = $next($request);
        $durationSeconds = (hrtime(true) - $startedAt) / 1_000_000_000;
        $route = $request->route()?->uri() ?? 'unmatched';

        $this->metrics->record(
            $request->method(),
            $route,
            $response->getStatusCode(),
            $durationSeconds,
        );

        return $response;
    }
}
