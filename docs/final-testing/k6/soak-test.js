import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE_URL, TOKENS, getHeaders } from './config.example.js';

export const options = {
    stages: [
        { duration: '10s', target: 6 },  // ramp-up konstan
        { duration: '50s', target: 6 },  // soak konstan selama durasi terukur
        { duration: '10s', target: 0 },  // graceful ramp-down
    ],
    thresholds: {
        http_req_failed: ['rate<0.02'],     // error rate harus sangat rendah (< 2%)
        http_req_duration: ['p(95)<450', 'avg<250'],
    },
};

export default function () {
    const authHeaders = getHeaders(TOKENS.customer);
    const publicHeaders = getHeaders();

    let resHealth = http.get(`${BASE_URL}/api/v1/health`, { headers: publicHeaders });
    check(resHealth, { 'health: status 200': (r) => r.status === 200 });

    if (TOKENS.customer) {
        let resDash = http.get(`${BASE_URL}/api/v1/dashboard`, { headers: authHeaders });
        check(resDash, { 'dashboard: status 200': (r) => r.status === 200 });

        let resItems = http.get(`${BASE_URL}/api/v1/service-items`, { headers: authHeaders });
        check(resItems, { 'catalog: status 200': (r) => r.status === 200 });
    }

    sleep(0.5);
}

