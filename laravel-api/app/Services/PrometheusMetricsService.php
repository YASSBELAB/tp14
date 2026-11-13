<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class PrometheusMetricsService
{
    private const BUCKETS = [0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10];

    public function record(string $method, string $route, int $status, float $durationSeconds): void
    {
        $path = storage_path('framework/cache/prometheus-metrics.json');
        File::ensureDirectoryExists(dirname($path));

        $file = fopen($path, 'c+');
        if ($file === false) {
            return;
        }

        try {
            if (! flock($file, LOCK_EX)) {
                return;
            }

            rewind($file);
            $metrics = json_decode(stream_get_contents($file) ?: '{}', true) ?: [];
            $key = hash('sha256', implode("\0", [$method, $route, (string) $status]));
            $labels = ['method' => $method, 'route' => $route, 'status' => (string) $status];

            $metrics[$key] ??= [
                'type' => 'http',
                'labels' => $labels,
                'count' => 0,
                'sum' => 0.0,
                'buckets' => array_fill_keys(array_map('strval', self::BUCKETS), 0),
            ];

            $metrics[$key]['count']++;
            $metrics[$key]['sum'] += $durationSeconds;
            foreach (self::BUCKETS as $bucket) {
                if ($durationSeconds <= $bucket) {
                    $metrics[$key]['buckets'][(string) $bucket]++;
                }
            }

            rewind($file);
            ftruncate($file, 0);
            fwrite($file, json_encode($metrics, JSON_THROW_ON_ERROR));
            fflush($file);
        } finally {
            flock($file, LOCK_UN);
            fclose($file);
        }
    }

    public function recordDatabaseQuery(float $durationSeconds, string $connection): void
    {
        $path = storage_path('framework/cache/prometheus-metrics.json');
        File::ensureDirectoryExists(dirname($path));

        $file = fopen($path, 'c+');
        if ($file === false) {
            return;
        }

        try {
            if (! flock($file, LOCK_EX)) {
                return;
            }

            rewind($file);
            $metrics = json_decode(stream_get_contents($file) ?: '{}', true) ?: [];
            $key = 'database:'.hash('sha256', $connection);
            $metrics[$key] ??= [
                'type' => 'database',
                'labels' => ['connection' => $connection],
                'count' => 0,
                'sum' => 0.0,
                'buckets' => array_fill_keys(array_map('strval', self::BUCKETS), 0),
            ];

            $metrics[$key]['count']++;
            $metrics[$key]['sum'] += $durationSeconds;
            foreach (self::BUCKETS as $bucket) {
                if ($durationSeconds <= $bucket) {
                    $metrics[$key]['buckets'][(string) $bucket]++;
                }
            }

            rewind($file);
            ftruncate($file, 0);
            fwrite($file, json_encode($metrics, JSON_THROW_ON_ERROR));
            fflush($file);
        } finally {
            flock($file, LOCK_UN);
            fclose($file);
        }
    }

    public function render(): string
    {
        $path = storage_path('framework/cache/prometheus-metrics.json');
        $metrics = [];

        if (is_file($path)) {
            $file = fopen($path, 'r');
            if ($file !== false) {
                try {
                    flock($file, LOCK_SH);
                    $metrics = json_decode(stream_get_contents($file) ?: '{}', true) ?: [];
                } finally {
                    flock($file, LOCK_UN);
                    fclose($file);
                }
            }
        }

        $lines = [
            '# HELP laravel_http_requests_total Total number of HTTP requests.',
            '# TYPE laravel_http_requests_total counter',
        ];

        foreach ($metrics as $metric) {
            if (($metric['type'] ?? 'http') !== 'http') {
                continue;
            }

            $lines[] = 'laravel_http_requests_total'.$this->formatLabels($metric['labels']).' '.$metric['count'];
        }

        $lines[] = '# HELP laravel_http_request_duration_seconds HTTP request duration in seconds.';
        $lines[] = '# TYPE laravel_http_request_duration_seconds histogram';

        foreach ($metrics as $metric) {
            if (($metric['type'] ?? 'http') !== 'http') {
                continue;
            }

            foreach (self::BUCKETS as $bucket) {
                $labels = $metric['labels'] + ['le' => (string) $bucket];
                $lines[] = 'laravel_http_request_duration_seconds_bucket'.$this->formatLabels($labels).' '.$metric['buckets'][(string) $bucket];
            }

            $lines[] = 'laravel_http_request_duration_seconds_bucket'.$this->formatLabels($metric['labels'] + ['le' => '+Inf']).' '.$metric['count'];
            $lines[] = 'laravel_http_request_duration_seconds_sum'.$this->formatLabels($metric['labels']).' '.$metric['sum'];
            $lines[] = 'laravel_http_request_duration_seconds_count'.$this->formatLabels($metric['labels']).' '.$metric['count'];
        }

        $lines[] = '# HELP laravel_database_query_duration_seconds Duration of Laravel database queries in seconds.';
        $lines[] = '# TYPE laravel_database_query_duration_seconds histogram';

        foreach ($metrics as $metric) {
            if (($metric['type'] ?? null) !== 'database') {
                continue;
            }

            foreach (self::BUCKETS as $bucket) {
                $labels = $metric['labels'] + ['le' => (string) $bucket];
                $lines[] = 'laravel_database_query_duration_seconds_bucket'.$this->formatLabels($labels).' '.$metric['buckets'][(string) $bucket];
            }

            $lines[] = 'laravel_database_query_duration_seconds_bucket'.$this->formatLabels($metric['labels'] + ['le' => '+Inf']).' '.$metric['count'];
            $lines[] = 'laravel_database_query_duration_seconds_sum'.$this->formatLabels($metric['labels']).' '.$metric['sum'];
            $lines[] = 'laravel_database_query_duration_seconds_count'.$this->formatLabels($metric['labels']).' '.$metric['count'];
        }

        return implode("\n", $lines)."\n";
    }

    private function formatLabels(array $labels): string
    {
        $formatted = [];
        foreach ($labels as $name => $value) {
            $escaped = addcslashes((string) $value, "\\\n\"");
            $formatted[] = $name.'="'.$escaped.'"';
        }

        return '{'.implode(',', $formatted).'}';
    }
}
