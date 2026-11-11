import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE_URL, TOKENS, getHeaders } from './config.example.js';

export const options = {
    stages: [
        { duration: '10s', target: 5 },  // ramp-up ke beban normal rendah
        { duration: '20s', target: 10 }, // sustain beban target normal
        { duration: '10s', target: 0 },  // ramp-down
    ],
    thresholds: {
        http_req_failed: ['rate<0.02'],     // kegagalan di bawah 2%
        http_req_duration: ['p(95)<500', 'p(90)<350', 'avg<250'], // latency terukur
    },
};

export default function () {
    const authHeaders = getHeaders(TOKENS.customer);
    const publicHeaders = getHeaders();

    // 1. Health check
    let resHealth = http.get(`${BASE_URL}/api/v1/health`, { headers: publicHeaders });
    check(resHealth, { 'health: status 200': (r) => r.status === 200 });

    // 2. Dashboard
    if (TOKENS.customer) {
        let resDash = http.get(`${BASE_URL}/api/v1/dashboard`, { headers: authHeaders });
        check(resDash, { 'dashboard: status 200': (r) => r.status === 200 });
    }

    // 3. Vehicles
    if (TOKENS.customer) {
        let resVehicles = http.get(`${BASE_URL}/api/v1/vehicles`, { headers: authHeaders });
        check(resVehicles, { 'vehicles: status 200': (r) => r.status === 200 });
    }

    // 4. Catalog
    if (TOKENS.customer) {
        let resItems = http.get(`${BASE_URL}/api/v1/service-items`, { headers: authHeaders });
        check(resItems, { 'catalog: status 200': (r) => r.status === 200 });
    }

    // 5. Payment settings
    if (TOKENS.customer) {
        let resPay = http.get(`${BASE_URL}/api/v1/payment-settings`, { headers: authHeaders });
        check(resPay, { 'pay-settings: status 200': (r) => r.status === 200 });
    }

    sleep(0.5);
}

