# Catatan Eksekusi Pengujian (Test Execution Log) — MotoCare
## Tahap Final Testing — Pemrograman Web Minggu 10

Dokumen ini mencatat urutan kronologis eksekusi seluruh perintah pengujian performa, regresi, dan audit yang dijalankan pada sesi Minggu 10.

---

## 1. Log Eksekusi Kronologis

| Timestamp (WIB) | Perintah / Eksekusi | Lingkungan / Target | Parameter / Konfigurasi | Hasil Eksekusi | Status |
|:---:|---|---|---|---|:---:|
| **20:29:52** | `php artisan test` | CLI / In-Memory SQLite | Full Test Suite (Feature + Unit) | **340 passed (801 assertions)** dalam 31.02s | **PASS** |
| **20:34:00** | `php artisan migrate --database=sqlite` | SQLite (`motocare`) | Migrasi skema tertunda | `2026_10_26_000001_create_payment_settings_table` DONE (20.19ms) | **PASS** |
| **20:34:27** | `php artisan db:seed --database=sqlite` | SQLite (`motocare`) | UserSeeder | 4 Akun seeded (Admin, Mekanik, Kurir, Customer) | **PASS** |
| **20:36:17** | Custom Seed Script | SQLite (`motocare`) | Vehicle & ServiceItem initial records | 1 Test Vehicle, 4 Service Items (Sparepart & Jasa) terbuat | **PASS** |
| **20:36:26** | `curl -X POST .../api/v1/auth/login` | Localhost:8000 | Customer login verification | Response 200 OK, `{"success": true}` | **PASS** |
| **20:38:21** | `k6 run smoke-test.js` | Localhost:8000 | 2 VUs, durasi 15s | 140 reqs, 0% error, p95: 82.26ms, checks: 100% | **PASS** |
| **20:38:52** | `k6 run load-test.js` | Localhost:8000 | 10 VUs, durasi 40s (3 stages) | 1,070 reqs, 0% error, p95: 306.04ms, checks: 100% | **PASS** |
| **20:39:53** | `k6 run stress-test.js` | Localhost:8000 | 25 VUs, durasi 50s (4 stages) | 2,244 reqs, 0% error, p95: 525.93ms, checks: 100% | **PASS** |
| **20:40:55** | `k6 run spike-test.js` | Localhost:8000 | 2 -> 25 -> 2 VUs, durasi 40s | 1,458 reqs, 0% error, p95: 305.13ms, checks: 100% | **PASS** |
| **20:41:46** | `k6 run soak-test.js` | Localhost:8000 | 6 VUs konstan, durasi 70s | 1,623 reqs, 0% error, p95: 104.84ms, checks: 100% | **PASS** |
| **20:43:12** | `k6 run breakpoint-test.js` | Localhost:8000 | 5 -> 15 -> 30 -> 45 VUs, durasi 60s | 3,423 reqs, 0% error, p95: 505.56ms, checks: 100% | **PASS** |

---

## 2. Ringkasan Volume Permintaan HTTP k6

- **Total Request Diuji:** **9,958 requests**
- **Total Durasi Uji Beban:** **277.2 detik**
- **Rata-rata Throughput:** **35.92 requests/detik**
- **Puncak Throughput Tertinggi:** **56.61 requests/detik (Breakpoint Test pada 45 VU)**
- **Total Error HTTP (4xx / 5xx selain throttle sengaja):** **0 request (0.00% error rate)**
- **Checks Passed:** **9,998 / 9,998 checks (100.00%)**

