// docs/final-testing/k6/config.example.js
// Template konfigurasi dan helper untuk pengujian performa k6 MotoCare

export const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export const TOKENS = {
    customer: __ENV.CUSTOMER_TOKEN || '',
    admin: __ENV.ADMIN_TOKEN || '',
    mechanic: __ENV.MECHANIC_TOKEN || '',
    courier: __ENV.COURIER_TOKEN || '',
};

export function getHeaders(token = null) {
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    };
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }
    return headers;
}

