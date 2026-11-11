import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE_URL, TOKENS, getHeaders } from './config.example.js';

export const options = {
    stages: [
        { duration: '10s', target: 2 },  // baseline normal
        { duration: '5s', target: 25 },  // spike tajam mendadak
        { duration: '10s', target: 25 }, // peak duration
        { duration: '5s', target: 2 },   // penurunan tajam / recovery
        { duration: '10s', target: 2 },  // observasi kestabilan pasca-spike
    ],
    thresholds: {
        http_req_failed: ['rate<0.05'], // toleransi error < 5% saat lonjakan
        http_req_duration: ['p(95)<2000'],
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

    sleep(0.4);
}

