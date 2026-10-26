# Cakupan Pengujian Endpoint API — MotoCare (MB-engkelQQ)

Dokumen ini memetakan seluruh route REST API v1 (`/api/v1/*`) beserta status pengujian, middleware otentikasi, dan tes feature pembuktiannya.

---

## 1. Ringkasan Cakupan

- **Total Endpoint API v1 Terdaftar:** **45 Endpoints**
- **Cakupan Pengujian:** **45 endpoint** tercakup dan lulus dalam automated feature tests; **7 endpoint inti** juga diverifikasi melalui live HTTP/curl probe.
- **Endpoint Gagal (FAIL):** **0**
- **Endpoint Tanpa Pengujian (NOT VERIFIED):** **0**

---

## 2. Matriks Detail Endpoint REST API v1

Keterangan Metode Pengujian:
- **Automated Feature Test**: Pengujian fungsional dan kontrak menggunakan PHPUnit di lingkungan testing terisolasi.
- **Live HTTP Test**: Pengujian request dan response secara langsung terhadap server aktif (`http://127.0.0.1:8000`) menggunakan HTTP client / curl.
- **Static Route Audit**: Verifikasi route definition, middleware binding, dan parameter binding via `php artisan route:list`.

| Method | URI Endpoint | Middleware | Otorisasi Role | Status Code | Metode Pengujian | Test Pembuktian | Status |
|---|---|---|---|:---:|---|---|:---:|
| `GET` | `/api/v1/health` | Public | Public | 200 | Live HTTP + Automated | `HealthController`, Live Curl | **PASS** |
| `POST` | `/api/v1/auth/login` | `throttle:5,1` | Public | 200, 422, 429 | Live HTTP + Automated | `AuthApiTest`, Live Curl (Throttle 429) | **PASS** |
| `POST` | `/api/v1/auth/logout` | `auth:sanctum` | Semua Role | 200, 401 | Live HTTP + Automated | `AuthApiTest`, Live Curl (Revocation) | **PASS** |
| `GET` | `/api/v1/auth/me` | `auth:sanctum` | Semua Role | 200, 401 | Live HTTP + Automated | `AuthApiTest`, Live Curl | **PASS** |
| `GET` | `/api/v1/vehicles` | `auth:sanctum` | Customer, Admin | 200 | Live HTTP + Automated | `VehicleApiTest`, Live Curl | **PASS** |
| `POST` | `/api/v1/vehicles` | `auth:sanctum` | Customer, Admin | 201, 422 | Automated Feature Test | `VehicleApiTest` | **PASS** |
| `GET` | `/api/v1/vehicles/{vehicle}` | `auth:sanctum` | Owner, Admin | 200, 403, 404 | Automated Feature Test | `VehicleApiTest` | **PASS** |
| `PUT/PATCH`| `/api/v1/vehicles/{vehicle}` | `auth:sanctum` | Owner, Admin | 200, 403, 422 | Automated Feature Test | `VehicleApiTest` | **PASS** |
| `DELETE` | `/api/v1/vehicles/{vehicle}` | `auth:sanctum` | Owner, Admin | 200, 403, 422 | Automated Feature Test | `VehicleApiTest` | **PASS** |
| `GET` | `/api/v1/bookings` | `auth:sanctum` | Customer, Admin | 200 | Automated Feature Test | `BookingApiTest` | **PASS** |
| `POST` | `/api/v1/bookings` | `auth:sanctum` | Customer, Admin | 201, 422 | Automated Feature Test | `BookingApiTest` | **PASS** |
| `GET` | `/api/v1/bookings/{booking}` | `auth:sanctum` | Owner, Staf, Admin | 200, 403 | Automated Feature Test | `BookingApiTest` | **PASS** |
| `POST` | `/api/v1/bookings/{booking}/confirm`| `auth:sanctum` | Admin Saja | 200, 403, 422 | Automated Feature Test | `BookingApiTest` | **PASS** |
| `POST` | `/api/v1/bookings/{booking}/cancel` | `auth:sanctum` | Owner, Admin | 200, 403, 422 | Automated Feature Test | `BookingApiTest` | **PASS** |
| `GET` | `/api/v1/service-orders` | `auth:sanctum` | Mekanik, Customer, Admin | 200 | Automated Feature Test | `ServiceOrderApiTest` | **PASS** |
| `GET` | `/api/v1/service-orders/{id}` | `auth:sanctum` | Owner, Mekanik, Admin | 200, 403 | Automated Feature Test | `ServiceOrderApiTest` | **PASS** |
| `POST` | `/api/v1/service-orders/{id}/start` | `auth:sanctum` | Mekanik Pemilik, Admin | 200, 403, 422 | Automated Feature Test | `ServiceOrderApiTest` | **PASS** |
| `POST` | `/api/v1/service-orders/{id}/diagnosis` | `auth:sanctum` | Mekanik Pemilik, Admin | 200, 403, 422 | Automated Feature Test | `ServiceOrderApiTest` | **PASS** |
| `POST` | `/api/v1/service-orders/{id}/items` | `auth:sanctum` | Mekanik Pemilik, Admin | 201, 403, 422 | Automated Feature Test | `ServiceOrderApiTest` | **PASS** |
| `POST` | `/api/v1/service-orders/{id}/complete` | `auth:sanctum` | Mekanik Pemilik, Admin | 200, 403, 422 | Automated Feature Test | `ServiceOrderApiTest` | **PASS** |
| `GET` | `/api/v1/service-items` | `auth:sanctum` | Semua Role | 200 | Automated Feature Test | `ServiceItemApiTest` | **PASS** |
| `POST` | `/api/v1/service-items` | `auth:sanctum` | Admin Saja | 201, 403, 422 | Automated Feature Test | `ServiceItemApiTest` | **PASS** |
| `GET` | `/api/v1/service-items/{item}` | `auth:sanctum` | Semua Role | 200 | Automated Feature Test | `ServiceItemApiTest` | **PASS** |
| `PUT/PATCH`| `/api/v1/service-items/{item}` | `auth:sanctum` | Admin Saja | 200, 403, 422 | Automated Feature Test | `ServiceItemApiTest` | **PASS** |
| `DELETE` | `/api/v1/service-items/{item}` | `auth:sanctum` | Admin Saja | 200, 403 | Automated Feature Test | `ServiceItemApiTest` | **PASS** |
| `PATCH` | `/api/v1/service-items/{item}/stock`| `auth:sanctum` | Admin Saja | 200, 403, 422 | Automated Feature Test | `ServiceItemApiTest` | **PASS** |
| `POST` | `/api/v1/service-orders/{id}/payments` | `auth:sanctum` | Customer Pemilik, Admin | 201, 403, 422 | Automated Feature Test | `PaymentApiTest` | **PASS** |
| `GET` | `/api/v1/payments/{payment}` | `auth:sanctum` | Owner, Admin | 200, 403 | Automated Feature Test | `PaymentApiTest` | **PASS** |
| `POST` | `/api/v1/payments/{payment}/verify` | `auth:sanctum` | Admin Saja | 200, 403, 422 | Automated Feature Test | `PaymentApiTest` | **PASS** |
| `POST` | `/api/v1/payments/{payment}/reject` | `auth:sanctum` | Admin Saja | 200, 403, 422 | Automated Feature Test | `PaymentApiTest` | **PASS** |
| `GET` | `/api/v1/payment-settings` | `auth:sanctum` | Semua (Customer: instruksi aktif) | 200 | Automated Feature Test | `PaymentSettingApiTest` | **PASS** |
| `PUT` | `/api/v1/payment-settings` | `auth:sanctum` | Admin Saja | 200, 403, 422 | Live HTTP + Automated | `PaymentSettingApiTest`, Live Curl (403 non-admin) | **PASS** |
| `GET` | `/api/v1/delivery-tasks` | `auth:sanctum` | Kurir, Customer, Admin | 200 | Automated Feature Test | `DeliveryApiTest` | **PASS** |
| `GET` | `/api/v1/delivery-tasks/{task}` | `auth:sanctum` | Kurir Pemilik, Customer, Admin | 200, 403 | Automated Feature Test | `DeliveryApiTest` | **PASS** |
| `POST` | `/api/v1/delivery-tasks/{task}/assign` | `auth:sanctum` | Admin Saja | 200, 403, 422 | Automated Feature Test | `DeliveryApiTest` | **PASS** |
| `POST` | `/api/v1/delivery-tasks/{task}/start` | `auth:sanctum` | Kurir Ditugaskan, Admin | 200, 403, 422 | Automated Feature Test | `DeliveryApiTest` | **PASS** |
| `POST` | `/api/v1/delivery-tasks/{task}/complete` | `auth:sanctum` | Kurir Ditugaskan, Admin | 200, 403, 422 | Automated Feature Test | `DeliveryApiTest` | **PASS** |
| `GET` | `/api/v1/delivery-settings` | `auth:sanctum` | Semua Role | 200 | Automated Feature Test | `DeliveryApiTest` | **PASS** |
| `PUT` | `/api/v1/delivery-settings` | `auth:sanctum` | Admin Saja | 200, 403, 422 | Automated Feature Test | `DeliveryApiTest` | **PASS** |
| `GET` | `/api/v1/invoices` | `auth:sanctum` | Ringkasan Sesuai Kepemilikan | 200 | Automated Feature Test | `InvoiceApiTest` | **PASS** |
| `GET` | `/api/v1/invoices/{order_id}` | `auth:sanctum` | Owner, Mekanik, Admin | 200, 403 | Automated Feature Test | `InvoiceApiTest` | **PASS** |
| `GET` | `/api/v1/notifications` | `auth:sanctum` | User Login Saja | 200 | Automated Feature Test | `NotificationApiTest` | **PASS** |
| `GET` | `/api/v1/notifications/unread` | `auth:sanctum` | User Login Saja | 200 | Automated Feature Test | `NotificationApiTest` | **PASS** |
| `POST` | `/api/v1/notifications/{id}/read` | `auth:sanctum` | Owner Notifikasi | 200, 403 | Automated Feature Test | `NotificationApiTest` | **PASS** |
| `GET` | `/api/v1/dashboard` | `auth:sanctum` | Semua (Adaptif per Role) | 200 | Live HTTP + Automated | `DashboardApiTest`, Live Curl (Customer, Mekanik, Kurir, Admin) | **PASS** |
