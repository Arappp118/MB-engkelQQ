import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE_URL, TOKENS, getHeaders } from './config.example.js';

export const options = {
    stages: [
        { duration: '10s', target: 5 },  // baseline
        { duration: '10s', target: 15 }, // step 1
        { duration: '15s', target: 30 }, // step 2
        { duration: '15s', target: 45 }, // step 3 (breakpoint exploration)
        { duration: '10s', target: 0 },  // graceful recovery
    ],
    thresholds: {
        http_req_failed: ['rate<0.15'], // toleransi breakpoint < 15%
        http_req_duration: ['p(95)<3500'],
    },
};

export default function () {
    const authHeaders = getHeaders(TOKENS.customer);
    const publicHeaders = getHeaders();

    let resHealth = http.get(`${BASE_URL}/api/v1/health`, { headers: publicHeaders });
    check(resHealth, { 'health: status 200': (r) => r.status === 200 });

    if (TOKENS.customer) {
        let resItems = http.get(`${BASE_URL}/api/v1/service-items`, { headers: authHeaders });
        check(resItems, { 'catalog: status 200': (r) => r.status === 200 });

        let resDash = http.get(`${BASE_URL}/api/v1/dashboard`, { headers: authHeaders });
        check(resDash, { 'dashboard: status 200': (r) => r.status === 200 });
    }

    sleep(0.3);
}

