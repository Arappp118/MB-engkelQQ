# Matriks Pengujian 45 Endpoint REST API v1 — MotoCare (MB-engkelQQ)
## Tahap Final Testing — Pemrograman Web Minggu 10

Dokumen ini memetakan seluruh 45 endpoint REST API v1 yang aktif terdaftar pada file `routes/api.php`, status pengujian, middleware otentikasi, otorisasi role, dan status hasil pengujian.

---

## 1. Ringkasan Endpoint

- **Total Endpoint REST API Terdaftar:** **45 Endpoints**
- **Endpoint Berstatus PASS:** **45 (100%)**
- **Endpoint Berstatus FAIL:** **0 (0%)**
- **Endpoint Berstatus NOT TESTED:** **0 (0%)**

---

## 2. Matriks Pengujian 45 Endpoint

| No | Method | URI Endpoint | Middleware | Otorisasi Role | Expected Status | Actual Status | Test Case File | Hasil |
|:---:|---|---|---|---|:---:|:---:|---|:---:|
| 1 | `GET` | `/api/v1/health` | Public | Public | 200 | 200 | `k6/smoke-test.js`, Live Probe | **PASS** |
| 2 | `POST` | `/api/v1/auth/login` | `throttle:5,1` | Public | 200, 422, 429 | 200, 422, 429 | `tests/Feature/Api/AuthApiTest.php` | **PASS** |
| 3 | `POST` | `/api/v1/auth/logout` | `auth:sanctum` | Semua Role | 200, 401 | 200, 401 | `tests/Feature/Api/AuthApiTest.php` | **PASS** |
| 4 | `GET` | `/api/v1/auth/me` | `auth:sanctum` | Semua Role | 200, 401 | 200, 401 | `tests/Feature/Api/AuthApiTest.php` | **PASS** |
| 5 | `GET` | `/api/v1/vehicles` | `auth:sanctum` | Customer, Admin | 200 | 200 | `tests/Feature/Api/VehicleApiTest.php` | **PASS** |
| 6 | `POST` | `/api/v1/vehicles` | `auth:sanctum` | Customer, Admin | 201, 422 | 201, 422 | `tests/Feature/Api/VehicleApiTest.php` | **PASS** |
| 7 | `GET` | `/api/v1/vehicles/{vehicle}` | `auth:sanctum` | Owner, Admin | 200, 403, 404 | 200, 403, 404 | `tests/Feature/Api/VehicleApiTest.php` | **PASS** |
| 8 | `PUT` | `/api/v1/vehicles/{vehicle}` | `auth:sanctum` | Owner, Admin | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/VehicleApiTest.php` | **PASS** |
| 9 | `DELETE`| `/api/v1/vehicles/{vehicle}` | `auth:sanctum` | Owner, Admin | 200, 403 | 200, 403 | `tests/Feature/Api/VehicleApiTest.php` | **PASS** |
| 10 | `GET` | `/api/v1/bookings` | `auth:sanctum` | Customer, Admin | 200 | 200 | `tests/Feature/Api/BookingApiTest.php` | **PASS** |
| 11 | `POST` | `/api/v1/bookings` | `auth:sanctum` | Customer, Admin | 201, 422 | 201, 422 | `tests/Feature/Api/BookingApiTest.php` | **PASS** |
| 12 | `GET` | `/api/v1/bookings/{booking}` | `auth:sanctum` | Owner, Staf, Admin | 200, 403, 404 | 200, 403, 404 | `tests/Feature/Api/BookingApiTest.php` | **PASS** |
| 13 | `POST` | `/api/v1/bookings/{booking}/confirm` | `auth:sanctum` | Admin Saja | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/BookingApiTest.php` | **PASS** |
| 14 | `POST` | `/api/v1/bookings/{booking}/cancel` | `auth:sanctum` | Owner, Admin | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/BookingApiTest.php` | **PASS** |
| 15 | `GET` | `/api/v1/service-orders` | `auth:sanctum` | Mekanik, Customer, Admin | 200 | 200 | `tests/Feature/Api/ServiceOrderApiTest.php` | **PASS** |
| 16 | `GET` | `/api/v1/service-orders/{service_order}` | `auth:sanctum` | Owner, Mekanik, Admin | 200, 403, 404 | 200, 403, 404 | `tests/Feature/Api/ServiceOrderApiTest.php` | **PASS** |
| 17 | `POST` | `/api/v1/service-orders/{service_order}/start` | `auth:sanctum` | Mekanik Ditugaskan, Admin | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/ServiceOrderApiTest.php` | **PASS** |
| 18 | `POST` | `/api/v1/service-orders/{service_order}/diagnosis` | `auth:sanctum` | Mekanik Ditugaskan, Admin | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/ServiceOrderApiTest.php` | **PASS** |
| 19 | `POST` | `/api/v1/service-orders/{service_order}/items` | `auth:sanctum` | Mekanik Ditugaskan, Admin | 201, 403, 422 | 201, 403, 422 | `tests/Feature/Api/ServiceOrderApiTest.php` | **PASS** |
| 20 | `POST` | `/api/v1/service-orders/{service_order}/complete` | `auth:sanctum` | Mekanik Ditugaskan, Admin | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/ServiceOrderApiTest.php` | **PASS** |
| 21 | `GET` | `/api/v1/service-items` | `auth:sanctum` | Semua Role | 200 | 200 | `tests/Feature/Api/ServiceItemApiTest.php` | **PASS** |
| 22 | `POST` | `/api/v1/service-items` | `auth:sanctum` | Admin Saja | 201, 403, 422 | 201, 403, 422 | `tests/Feature/Api/ServiceItemApiTest.php` | **PASS** |
| 23 | `GET` | `/api/v1/service-items/{service_item}` | `auth:sanctum` | Semua Role | 200, 404 | 200, 404 | `tests/Feature/Api/ServiceItemApiTest.php` | **PASS** |
| 24 | `PUT` | `/api/v1/service-items/{service_item}` | `auth:sanctum` | Admin Saja | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/ServiceItemApiTest.php` | **PASS** |
| 25 | `DELETE`| `/api/v1/service-items/{service_item}` | `auth:sanctum` | Admin Saja | 200, 403 | 200, 403 | `tests/Feature/Api/ServiceItemApiTest.php` | **PASS** |
| 26 | `PATCH`| `/api/v1/service-items/{service_item}/stock` | `auth:sanctum` | Admin Saja | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/ServiceItemApiTest.php` | **PASS** |
| 27 | `POST` | `/api/v1/service-orders/{service_order}/payments` | `auth:sanctum` | Customer Pemilik, Admin | 201, 403, 422 | 201, 403, 422 | `tests/Feature/Api/PaymentApiTest.php` | **PASS** |
| 28 | `GET` | `/api/v1/payments/{payment}` | `auth:sanctum` | Owner, Admin | 200, 403, 404 | 200, 403, 404 | `tests/Feature/Api/PaymentApiTest.php` | **PASS** |
| 29 | `POST` | `/api/v1/payments/{payment}/verify` | `auth:sanctum` | Admin Saja | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/PaymentApiTest.php` | **PASS** |
| 30 | `POST` | `/api/v1/payments/{payment}/reject` | `auth:sanctum` | Admin Saja | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/PaymentApiTest.php` | **PASS** |
| 31 | `GET` | `/api/v1/delivery-tasks` | `auth:sanctum` | Kurir, Customer, Admin | 200 | 200 | `tests/Feature/Api/DeliveryApiTest.php` | **PASS** |
| 32 | `GET` | `/api/v1/delivery-tasks/{delivery_task}` | `auth:sanctum` | Kurir Pemilik, Customer, Admin | 200, 403, 404 | 200, 403, 404 | `tests/Feature/Api/DeliveryApiTest.php` | **PASS** |
| 33 | `POST` | `/api/v1/delivery-tasks/{delivery_task}/assign` | `auth:sanctum` | Admin Saja | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/DeliveryApiTest.php` | **PASS** |
| 34 | `POST` | `/api/v1/delivery-tasks/{delivery_task}/start` | `auth:sanctum` | Kurir Ditugaskan, Admin | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/DeliveryApiTest.php` | **PASS** |
| 35 | `POST` | `/api/v1/delivery-tasks/{delivery_task}/complete`| `auth:sanctum` | Kurir Ditugaskan, Admin | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/DeliveryApiTest.php` | **PASS** |
| 36 | `GET` | `/api/v1/delivery-settings` | `auth:sanctum` | Semua Role | 200 | 200 | `tests/Feature/Api/DeliveryApiTest.php` | **PASS** |
| 37 | `PUT` | `/api/v1/delivery-settings` | `auth:sanctum` | Admin Saja | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/DeliveryApiTest.php` | **PASS** |
| 38 | `GET` | `/api/v1/payment-settings` | `auth:sanctum` | Semua Role | 200 | 200 | `tests/Feature/Api/PaymentSettingApiTest.php`| **PASS** |
| 39 | `PUT` | `/api/v1/payment-settings` | `auth:sanctum` | Admin Saja | 200, 403, 422 | 200, 403, 422 | `tests/Feature/Api/PaymentSettingApiTest.php`| **PASS** |
| 40 | `GET` | `/api/v1/invoices` | `auth:sanctum` | Terfilter Sesuai Role | 200 | 200 | `tests/Feature/Api/InvoiceApiTest.php` | **PASS** |
| 41 | `GET` | `/api/v1/invoices/{service_order}` | `auth:sanctum` | Owner, Mekanik, Admin | 200, 403, 404 | 200, 403, 404 | `tests/Feature/Api/InvoiceApiTest.php` | **PASS** |
| 42 | `GET` | `/api/v1/notifications` | `auth:sanctum` | User Terotentikasi | 200 | 200 | `tests/Feature/Api/NotificationApiTest.php` | **PASS** |
| 43 | `GET` | `/api/v1/notifications/unread` | `auth:sanctum` | User Terotentikasi | 200 | 200 | `tests/Feature/Api/NotificationApiTest.php` | **PASS** |
| 44 | `POST` | `/api/v1/notifications/{notification}/read` | `auth:sanctum` | Owner Notifikasi | 200, 403, 404 | 200, 403, 404 | `tests/Feature/Api/NotificationApiTest.php` | **PASS** |
| 45 | `GET` | `/api/v1/dashboard` | `auth:sanctum` | Semua (Adaptif per Role) | 200 | 200 | `tests/Feature/Api/DashboardApiTest.php` | **PASS** |

---

## 3. Catatan Keamanan & Kepatuhan Kontrak API

- Seluruh response error mengembalikan format JSON konsisten: `{"success": false, "message": "..."}` dengan HTTP Status Code semantik (401, 403, 404, 422, 429).
- Token otentikasi Sanctum di-hash dan disimpan aman dalam tabel `personal_access_tokens`.
- Endpoint publik murni dibatasi hanya pada `/api/v1/health` dan `/api/v1/auth/login`.

