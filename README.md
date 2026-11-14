# TP14 — Observabilité des API backend

Ce dépôt contient les trois applications backend (Spring Boot, Express et Laravel) ainsi que leur pile de supervision Prometheus, Grafana, Loki, Alloy et Tempo.


# LAB 14 – Complete Observability Architecture with Docker

## 📌 Overview

This laboratory focuses on implementing a complete observability architecture for the backend applications developed in previous labs.

The objective is to centralize monitoring, metrics collection, visualization, and log management for three backend applications:

- Spring Boot
- Node.js / Express
- PHP / Laravel

The infrastructure is containerized and orchestrated using Docker Compose.

## 🎯 Objectives

- Collect application and system metrics using **Prometheus**.
- Visualize metrics and monitor application health using **Grafana**.
- Centralize application logs using **Grafana Loki**.
- Collect and forward logs using **Grafana Alloy / Promtail**.
- Configure a centralized observability infrastructure.
- Orchestrate all monitoring services using Docker Compose.

## 🏗️ Architecture

The observability stack consists of the following components:

| Component | Role |
|---|---|
| Spring Boot | Backend application |
| Node.js / Express | Backend application |
| Laravel | Backend application |
| Prometheus | Metrics collection and time-series storage |
| Grafana | Metrics visualization and dashboards |
| Loki | Centralized log storage |
| Grafana Alloy / Promtail | Log collection and forwarding |
| Docker Compose | Infrastructure orchestration |

### Observability pillars

**1. Metrics**
- CPU and memory usage.
- HTTP request counts.
- Request execution duration.
- Application performance.

**2. Logs**
- Application events.
- Errors and exceptions.
- HTTP request logs.
- Structured JSON logs.

**3. Traces**
- Request execution paths across application components.
- Identification of bottlenecks and latency.
- Understanding interactions between services.

## 🛠️ Technologies

- Docker
- Docker Compose
- Prometheus
- Grafana
- Grafana Loki
- Grafana Alloy / Promtail
- Spring Boot
- Node.js
- Express.js
- PHP
- Laravel
- MySQL

## 📋 Prerequisites

Make sure the following tools are installed:

- Docker Desktop
- Docker Compose
- Git
- Postman, cURL, or Apache JMeter (for generating HTTP traffic)

Verify Docker installation:

```bash
docker --version
docker-compose --version
```

## 🚀 Getting Started

### 1. Clone the repository

```bash
git clone YOUR_REPOSITORY_URL
cd YOUR_PROJECT_FOLDER
```

### 2. Start the infrastructure

```bash
docker compose up -d --build
```

### 3. Check running containers

```bash
docker compose ps
```

### 4. View container logs

```bash
docker compose logs -f
```

### 5. Access monitoring interfaces

| Service | URL |
|---|---|
| Prometheus | http://localhost:9090 |
| Grafana | http://localhost:3000 |
| Loki | http://localhost:3100 |

*Note: Access URLs and ports depend on the Docker Compose configuration.*

## 📊 Monitoring Workflow

1. Backend applications expose their metrics through dedicated `/metrics` endpoints.
2. Prometheus periodically scrapes the exposed metrics.
3. Grafana connects to Prometheus to visualize collected metrics.
4. Grafana Alloy / Promtail collects application logs.
5. Loki receives and stores centralized logs.
6. Grafana provides a unified interface for exploring metrics and logs.

## 🧪 Testing

Generate HTTP traffic using Postman, cURL, or Apache JMeter to observe application activity.

Example:

```bash
curl http://localhost:8081/api/products
```

Check the Prometheus targets:

```text
http://localhost:9090/targets
```

Verify that the configured application targets are successfully scraped.

## 🐳 Docker Management

Stop the infrastructure:

```bash
docker compose stop
```

Restart the infrastructure:

```bash
docker compose start
```

Stop and remove containers:

```bash
docker compose down
```

View resource consumption:

```bash
docker stats
```

## 📚 Key Concepts

- Observability vs Monitoring
- Metrics collection and scraping
- Time-series databases
- Centralized logging
- Structured JSON logging
- Prometheus exporters
- Grafana dashboards
- Loki log aggregation
- Docker Compose orchestration

## 👨‍💻 Author

**Yassir Bellaachia**

Software Development – Backend & DevOps

---

*LAB 14 – Observability | Docker | Prometheus | Grafana | Loki*

Grafana affiche les sources de données configurées : Loki pour les logs, Prometheus pour les métriques et Tempo pour les traces.

<img width="1279" height="636" alt="Screenshot 2026-11-13 182159" src="https://github.com/user-attachments/assets/a7b1684b-90b1-459f-923a-d1db8f2ddd2b" />


La page “Targets” de Prometheus confirme que les trois API — Laravel, Node.js et Spring Boot — sont joignables et que leur collecte de métriques est opérationnelle (UP).



<img width="1279" height="639" alt="Screenshot 2026-11-13 145942" src="https://github.com/user-attachments/assets/b2ace1ae-f24b-4f2e-b04a-847ba9c1f6c0" />


Grafana est connecté à Prometheus, défini comme source de données par défaut, et prêt à explorer les métriques des API.



<img width="1278" height="631" alt="Screenshot 2026-11-13 150013" src="https://github.com/user-attachments/assets/248cff9e-2785-4ee8-bdcf-5e00dd322ca9" />


L’endpoint /metrics de Node.js expose correctement les métriques Prometheus, notamment le compteur de requêtes HTTP et l’histogramme de leur durée.


<img width="1279" height="702" alt="Screenshot 2026-11-13 150045" src="https://github.com/user-attachments/assets/a42f9b7e-4003-45a4-9bcf-49817ad351d0" />



L’endpoint /metrics de Laravel expose correctement un compteur de requêtes HTTP et un histogramme de leur durée, au format Prometheus.

<img width="1224" height="592" alt="Screenshot 2026-11-13 150101" src="https://github.com/user-attachments/assets/dcf1a2b3-3f79-4bc9-8a33-7e97cee8ebf6" />



L’endpoint /actuator/prometheus de Spring Boot expose les métriques JVM, système et serveur Tomcat, étiquetées avec application="spring-backend-api".


<img width="1137" height="625" alt="Screenshot 2026-11-13 150121" src="https://github.com/user-attachments/assets/f723bd8f-a5a4-4afd-9e9e-96dc75e5eca0" />


Le tableau de bord Spring Boot affiche le total des requêtes HTTP et l’évolution de l’utilisation mémoire de la JVM, ventilée par espace mémoire.


<img width="1279" height="634" alt="Screenshot 2026-11-13 151005" src="https://github.com/user-attachments/assets/6d799a63-f149-4955-9f73-267ef7bc797e" />


Le tableau de bord Node.js suit le volume des requêtes, leur taux par code HTTP, les erreurs 5xx, la latence p95 et l’utilisation de l’event loop.


<img width="1279" height="587" alt="Screenshot 2026-11-13 152307" src="https://github.com/user-attachments/assets/612dd969-e1ba-4036-aa22-f95fd17c5936" />



Le tableau de bord Laravel montre une requête HTTP enregistrée ; les graphiques de durée restent vides faute de requêtes SQL ou HTTP mesurées sur la période affichée.



<img width="1234" height="605" alt="Screenshot 2026-11-13 152524" src="https://github.com/user-attachments/assets/2de43d59-a2dc-4921-8fc1-15ce289787b9" />



Dans Grafana Explore, les logs Loki montrent les requêtes Node.js et filtrent les erreurs HTTP 500 de Spring Boot.


<img width="1275" height="613" alt="Screenshot 2026-11-13 161105" src="https://github.com/user-attachments/assets/dc53d822-1e39-4d89-b956-da700e262a0b" />



Loki centralise les logs JSON des requêtes Node.js : la requête LogQL {job="docker"} | json | event="http_request" affiche les événements HTTP collectés.



<img width="1265" height="594" alt="Screenshot 2026-11-13 164000" src="https://github.com/user-attachments/assets/ff8ad54b-fe0f-48a6-b0d4-41077cf64f54" />


Le tableau de bord Node.js montre le pic de trafic généré par le test de charge, avec le taux de requêtes, la latence p95 et l’utilisation de l’event loop.


<img width="1277" height="618" alt="Screenshot 2026-11-13 165643" src="https://github.com/user-attachments/assets/9362f740-31c5-4411-852e-c9fd0018f4ac" />



Loki affiche les journaux des requêtes Spring Boot vers /__simulation/npe, qui ont généré des réponses HTTP 500 pendant le scénario de test.


<img width="1256" height="624" alt="Screenshot 2026-11-13 171641" src="https://github.com/user-attachments/assets/00370b5d-808b-47be-b327-3b678a7592a1" />



Le tableau de bord Laravel est ouvert, mais aucun échantillon n’est disponible sur la période affichée pour les requêtes HTTP ou SQL.


<img width="1237" height="663" alt="lararrete" src="https://github.com/user-attachments/assets/bd3f428b-a9e0-4458-96ed-7428ad89beec" />



La requête up{job="laravel-app"} montre que Prometheus a récupéré la métrique de disponibilité de Laravel, avec des périodes où la cible est 1 (disponible).



<img width="1262" height="562" alt="laraarrete" src="https://github.com/user-attachments/assets/ee734c19-241a-4223-81ec-e081bc1b1dba" />



a requête PromQL calcule la durée moyenne de l’exécution SQL product_search à partir des métriques _sum et _count ; le graphique montre une mesure d’environ 0,12 seconde.]



<img width="1255" height="659" alt="Screenshot 2026-11-13 173803" src="https://github.com/user-attachments/assets/2b0dd98d-483a-41cb-9085-2ea2f98156d1" />




### **Réflexion : Quels sont les compromis en termes de performance et de stockage entre les logs textuels non structurés et les logs JSON structurés ?**

Les **logs textuels non structurés** sont simples à générer et nécessitent généralement moins de traitement lors de leur création. Cependant, ils sont plus difficiles à analyser, filtrer et rechercher automatiquement.

Les **logs JSON structurés** permettent d'organiser les informations sous forme de champs (date, niveau, événement, message, etc.). Ils facilitent leur exploitation avec des outils comme Grafana et Loki, mais nécessitent davantage de ressources pour leur génération et leur traitement. Ils peuvent également occuper plus d'espace de stockage à cause de leur structure et des noms des champs.

**Conclusion :** Les logs textuels sont adaptés aux applications simples, tandis que les logs JSON sont préférables pour les applications nécessitant une supervision avancée, une recherche rapide et une analyse automatisée.
