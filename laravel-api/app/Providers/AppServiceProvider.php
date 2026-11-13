<?php

namespace App\Providers;

use App\Services\PrometheusMetricsService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(PrometheusMetricsService $metrics): void
    {
        if (! $this->app->runningInConsole()) {
            DB::listen(function (QueryExecuted $query) use ($metrics): void {
                $metrics->recordDatabaseQuery($query->time / 1000, $query->connectionName);
            });
        }

        if ($this->app->environment('production') || env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }
    }
}
