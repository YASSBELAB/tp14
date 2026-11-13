import express from 'express';
import cors from 'cors';
import 'dotenv/config';
import client from 'prom-client';
import { performance } from 'node:perf_hooks';

import productRoutes from './routes/product.routes.js';
import categoryRoutes from './routes/category.routes.js';
import authRoutes from './routes/authRoutes.js';

import { authenticate } from './middlewares/auth.js';
import { requireApiAuth } from './middlewares/apiKey.middleware.js';
import { errorHandler } from './middlewares/error.middleware.js';
import setupSwagger from './swagger.js';

const app = express();

// Prometheus metrics
client.collectDefaultMetrics({ timeout: 5000 });

const httpRequestsTotal = new client.Counter({
    name: 'nodejs_http_requests_total',
    help: 'Total number of HTTP requests',
    labelNames: ['method', 'route', 'status']
});

const httpRequestDuration = new client.Histogram({
    name: 'nodejs_http_request_duration_seconds',
    help: 'HTTP request duration in seconds',
    labelNames: ['method', 'route', 'status'],
    buckets: [0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5]
});

const eventLoopUtilization = new client.Gauge({
    name: 'nodejs_event_loop_utilization_ratio',
    help: 'Fraction of the measurement interval during which the Node.js event loop was active',
});

let previousEventLoopUtilization = performance.eventLoopUtilization();
const eventLoopUtilizationTimer = setInterval(() => {
    const current = performance.eventLoopUtilization();
    const activeDelta = current.active - previousEventLoopUtilization.active;
    const idleDelta = current.idle - previousEventLoopUtilization.idle;
    const interval = activeDelta + idleDelta;

    eventLoopUtilization.set(interval > 0 ? activeDelta / interval : 0);
    previousEventLoopUtilization = current;
}, 5000);
eventLoopUtilizationTimer.unref();

app.use((req, res, next) => {
    // Keep Prometheus scrapes from inflating the application request counters.
    if (req.path === '/metrics') {
        return next();
    }

    const startedAt = process.hrtime.bigint();

    res.on('finish', () => {
        const route = req.route ? `${req.baseUrl}${req.route.path}` : 'unmatched';
        const labels = [req.method, route, String(res.statusCode)];
        httpRequestsTotal.labels(...labels).inc();
        httpRequestDuration.labels(...labels).observe(
            Number(process.hrtime.bigint() - startedAt) / 1_000_000_000
        );
        console.log(JSON.stringify({
            timestamp: new Date().toISOString(),
            level: 'info',
            event: 'http_request',
            method: req.method,
            route,
            path: req.path,
            ip: req.ip,
            status: res.statusCode,
            duration_ms: Number(process.hrtime.bigint() - startedAt) / 1_000_000
        }));
    });
    next();
});

app.get('/metrics', async (req, res, next) => {
    try {
        res.setHeader('Content-Type', client.register.contentType);
        res.send(await client.register.metrics());
    } catch (error) {
        next(error);
    }
});

// Global middleware
app.use(cors({
    origin: process.env.CORS_ORIGIN || 'http://localhost:5173',
    methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
    allowedHeaders: ['Content-Type', 'Authorization']
}));

app.use(express.json());

// Test route
app.get('/', (req, res) => {
    res.json({
        message: 'Welcome to Product REST API - LAB 7'
    });
});

// Healthcheck route
app.get('/health', (req, res) => {
    res.status(200).json({ status: 'ok' });
});

// Secure all three APIs (/api/products, /api/categories, /api/auth) with API_KEY / Bearer auth
app.use('/api', requireApiAuth);

// LAB 6: Product routes (authentication required)
app.use('/api/products', authenticate, productRoutes);

// LAB 6: Category routes
app.use('/api/categories', categoryRoutes);

// LAB 7: Authentication routes
app.use('/api/auth', authRoutes);

// Swagger UI documentation
setupSwagger(app);

// Global error handler (must be last)
app.use(errorHandler);

export default app;
