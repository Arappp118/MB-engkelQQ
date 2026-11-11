import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE_URL, TOKENS, getHeaders } from './config.example.js';

export const options = {
    vus: 2,
    duration: '15s',
    thresholds: {
        http_req_failed: ['rate<0.01'], // kegagalan < 1%
        http_req_duration: ['p(95)<300'], // 95% response di bawah 300ms
    },
};

export default function () {
    const authHeaders = getHeaders(TOKENS.customer);
    const publicHeaders = getHeaders();

    // 1. Health check (Public)
    let resHealth = http.get(`${BASE_URL}/api/v1/health`, { headers: publicHeaders });
    check(resHealth, {
        'health: status is 200': (r) => r.status === 200,
        'health: success flag is true': (r) => {
            try { return JSON.parse(r.body).success === true; } catch (e) { return false; }
        },
    });

    // 2. Auth me (Protected)
    if (TOKENS.customer) {
        let resMe = http.get(`${BASE_URL}/api/v1/auth/me`, { headers: authHeaders });
        check(resMe, {
            'me: status is 200': (r) => r.status === 200,
            'me: returns customer data': (r) => {
                try { return JSON.parse(r.body).data.email !== undefined; } catch (e) { return false; }
            },
        });
    }

    // 3. Vehicles (Protected)
    if (TOKENS.customer) {
        let resVehicles = http.get(`${BASE_URL}/api/v1/vehicles`, { headers: authHeaders });
        check(resVehicles, {
            'vehicles: status is 200': (r) => r.status === 200,
        });
    }

    // 4. Service items catalog (Protected)
    if (TOKENS.customer) {
        let resItems = http.get(`${BASE_URL}/api/v1/service-items`, { headers: authHeaders });
        check(resItems, {
            'service-items: status is 200': (r) => r.status === 200,
        });
    }

    // 5. Dashboard (Protected)
    if (TOKENS.customer) {
        let resDashboard = http.get(`${BASE_URL}/api/v1/dashboard`, { headers: authHeaders });
        check(resDashboard, {
            'dashboard: status is 200': (r) => r.status === 200,
        });
    }

    // 6. Settings (Payment & Delivery)
    if (TOKENS.customer) {
        let resPay = http.get(`${BASE_URL}/api/v1/payment-settings`, { headers: authHeaders });
        check(resPay, {
            'payment-settings: status is 200': (r) => r.status === 200,
        });

        let resDelivery = http.get(`${BASE_URL}/api/v1/delivery-settings`, { headers: authHeaders });
        check(resDelivery, {
            'delivery-settings: status is 200': (r) => r.status === 200,
        });
    }

    sleep(1);
}

