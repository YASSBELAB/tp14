import client from 'prom-client';

export const complexSqlQueryDuration = new client.Histogram({
    name: 'nodejs_complex_sql_query_duration_seconds',
    help: 'Execution time of the complex product search SQL query in seconds.',
    labelNames: ['query', 'outcome'],
    buckets: [0.001, 0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5]
});

export async function observeSqlQuery(queryName, executeQuery) {
    const startedAt = process.hrtime.bigint();
    let outcome = 'success';

    try {
        return await executeQuery();
    } catch (error) {
        outcome = 'error';
        throw error;
    } finally {
        const durationSeconds = Number(process.hrtime.bigint() - startedAt) / 1_000_000_000;
        complexSqlQueryDuration.labels(queryName, outcome).observe(durationSeconds);
    }
}
