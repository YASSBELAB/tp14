# Requêtes PromQL

Dans l’interface Prometheus (par défaut `http://localhost:9090`), ouvrez **Graph** et exécutez ces requêtes après que Prometheus a collecté des métriques.

## Débit des requêtes

Requêtes Express par route, en requêtes par seconde :

```promql
sum by (route) (rate(nodejs_http_requests_total[1m]))
```

Nombre de requêtes traitées par Express au cours de la dernière heure :

```promql
sum(increase(nodejs_http_requests_total[1h]))
```

Nombre de requêtes Laravel par route au cours de la dernière heure :

```promql
sum by (route) (increase(laravel_http_requests_total[1h]))
```

## JVM

Mémoire heap actuellement utilisée par l’application Spring :

```promql
jvm_memory_used_bytes{area="heap"}
```

## Latence moyenne

Durée moyenne des requêtes Spring sur cinq minutes :

```promql
rate(http_server_requests_seconds_sum[5m])
/ rate(http_server_requests_seconds_count[5m])
```

Durée moyenne Express, regroupée par route :

```promql
sum by (route) (rate(nodejs_http_request_duration_seconds_sum[5m]))
/ sum by (route) (rate(nodejs_http_request_duration_seconds_count[5m]))
```

Durée moyenne Laravel, regroupée par route :

```promql
sum by (route) (rate(laravel_http_request_duration_seconds_sum[5m]))
/ sum by (route) (rate(laravel_http_request_duration_seconds_count[5m]))
```

## Percentile 95 de latence

Le percentile 95 pour Express :

```promql
histogram_quantile(
  0.95,
  sum by (le, route) (rate(nodejs_http_request_duration_seconds_bucket[5m]))
)
```

Le percentile 95 pour Spring :

```promql
histogram_quantile(
  0.95,
  sum by (le, uri) (rate(http_server_requests_seconds_bucket[5m]))
)
```

Le percentile 95 pour Laravel :

```promql
histogram_quantile(
  0.95,
  sum by (le, route) (rate(laravel_http_request_duration_seconds_bucket[5m]))
)
```

Les compteurs et histogrammes commencent à zéro au démarrage de chaque application. `rate` et `increase` ont besoin d’au moins deux collectes dans leur fenêtre pour produire une valeur utile.
