import http from 'k6/http';
import { check } from 'k6';

export const options = {
  scenarios: {
    nodejs_100_rps: {
      executor: 'constant-arrival-rate',
      rate: 100,
      timeUnit: '1s',
      duration: '1m',
      preAllocatedVUs: 100,
      maxVUs: 300,
      gracefulStop: '10s',
    },
  },
};

export default function () {
  const response = http.get('http://nodejs-backend:3000/health');
  check(response, {
    'health endpoint returns 200': (res) => res.status === 200,
  });
}
