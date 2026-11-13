# TP14 — Observabilité des API backend

Ce dépôt contient les trois applications backend (Spring Boot, Express et Laravel) ainsi que leur pile de supervision Prometheus, Grafana, Loki, Alloy et Tempo.

## Rendu du laboratoire

Les quatre livrables, les instructions de démarrage et les captures sont dans [`rendu/04-README.md`](rendu/04-README.md). L’archive complète à remettre est [`rendu/rendu-laboratoire.zip`](rendu/rendu-laboratoire.zip).

Pour démarrer localement :

```powershell
Copy-Item .\rendu\.env.example .\.env
docker compose up -d --build
```

Les services et étapes de validation sont détaillés dans le README du rendu.
