<?php

use Illuminate\Support\Facades\Route;
use App\Services\PrometheusMetricsService;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/metrics', function (PrometheusMetricsService $metrics) {
    return response($metrics->render(), 200)
        ->header('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
});
