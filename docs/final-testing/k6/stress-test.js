import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE_URL, TOKENS, getHeaders } from './config.example.js';

export const options = {
    stages: [
        { duration: '10s', target: 10 }, // start di atas normal
        { duration: '15s', target: 20 }, // tingkatkan ke level stress
        { duration: '15s', target: 25 }, // tahan di batas atas aman server lokal
        { duration: '10s', target: 0 },  // recovery / ramp-down
    ],
    thresholds: {
        http_req_failed: ['rate<0.05'], // error rate maksimal 5%
        http_req_duration: ['p(95)<1500', 'avg<800'],
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

