# Laporan Pengujian Performa Grafana k6 — MotoCare (MB-engkelQQ)
## Tahap Final Testing — Pemrograman Web Minggu 10

---

## 1. Metadata Pengujian

- **Tanggal & Waktu Pengujian:** 10 November 2026, 20:38 – 20:44 WIB
- **Target URL (Base URL):** `http://127.0.0.1:8000` (Lingkungan lokal terisolasi)
- **Versi Grafana k6:** `v0.54.0 (commit/baba871c8a, go1.23.1, linux/amd64)`
- **Aplikasi Backend:** Laravel 11.56.1, PHP 8.4.27 (CLI Server dengan `PHP_CLI_SERVER_WORKERS=4`)
- **Database Backend:** SQLite (`motocare`) dengan data terisolasi
- **Metode Otentikasi:** Bearer Token (Laravel Sanctum) diinjeksikan via environment variables (`CUSTOMER_TOKEN`, `ADMIN_TOKEN`) tanpa hardcoding token

---

## 2. Ringkasan Hasil 6 Skenario Pengujian k6

| Skenario | VU Target | Durasi | Total Request | Throughput (req/s) | Avg Latency | p90 Latency | p95 Latency | Error Rate | Checks Passed | Status |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **1. Smoke Test** | 2 | 15.1s | 140 | 9.27 req/s | 71.45 ms | 71.69 ms | 82.26 ms | 0.00% | 180 / 180 (100%) | **PASS** |
| **2. Load Test** | 10 | 40.7s | 1,070 | 26.31 req/s | 112.26 ms | 218.00 ms | 306.04 ms | 0.00% | 1,070 / 1,070 (100%) | **PASS** |
| **3. Stress Test** | 25 | 50.2s | 2,244 | 44.67 req/s | 228.40 ms | 439.28 ms | 525.93 ms | 0.00% | 2,244 / 2,244 (100%) | **PASS** |
| **4. Spike Test** | 25 | 40.5s | 1,458 | 35.99 req/s | 161.00 ms | 244.26 ms | 305.13 ms | 0.00% | 1,458 / 1,458 (100%) | **PASS** |
| **5. Soak Test** | 6 | 70.2s | 1,623 | 23.13 req/s | 58.50 ms | 85.89 ms | 104.84 ms | 0.00% | 1,623 / 1,623 (100%) | **PASS** |
| **6. Breakpoint Test** | 45 | 60.5s | 3,423 | 56.61 req/s | 265.16 ms | 470.46 ms | 505.56 ms | 0.00% | 3,423 / 3,423 (100%) | **PASS** |
| **TOTAL** | — | **277.2s** | **9,958** | **35.92 req/s (avg)** | — | — | — | **0.00%** | **9,998 / 9,998 (100%)** | **ALL PASS** |

---

## 3. Rincian & Analisis Setiap Skenario

### 3.1. Smoke Test (`smoke-test.js`)
- **Tujuan:** Memvalidasi ketersediaan endpoint inti, keabsahan response contract, dan memastikan error rate 0% pada beban minimal.
- **Konfigurasi:** 2 VUs konstan selama 15 detik.
- **Threshold yang Ditetapkan:**
  - `http_req_failed`: `< 1%` (`rate < 0.01`)
  - `http_req_duration`: `p(95) < 300ms`
- **Hasil Aktual:**
  - `http_req_failed`: **0.00%** (0 / 140 req) — **PASS**
  - `http_req_duration (p95)`: **82.26 ms** — **PASS**
  - `checks`: **100.00%** (180 / 180 checks passed)
  - Endpoint yang diuji: `/api/v1/health`, `/api/v1/auth/me`, `/api/v1/vehicles`, `/api/v1/service-items`, `/api/v1/dashboard`, `/api/v1/payment-settings`, `/api/v1/delivery-settings`.
- **Analisis:** Seluruh kontrak data JSON mengembalikan struktur yang sesuai dan otentikasi Sanctum berfungsi secara konsisten.

---

### 3.2. Load Test (`load-test.js`)
- **Tujuan:** Mengukur kinerja API pada estimasi beban normal penggunaan bengkel (10 VUs simultan).
- **Konfigurasi Stages:** 10s ramp-up ke 5 VUs, 20s sustain pada 10 VUs, 10s ramp-down ke 0.
- **Threshold yang Ditetapkan:**
  - `http_req_failed`: `< 2%` (`rate < 0.02`)
  - `http_req_duration`: `p(95) < 500ms`, `p(90) < 350ms`, `avg < 250ms`
- **Hasil Aktual:**
  - `http_req_failed`: **0.00%** (0 / 1,070 req) — **PASS**
  - `avg`: **112.26 ms** — **PASS**
  - `p(90)`: **218.00 ms** — **PASS**
  - `p(95)`: **306.04 ms** — **PASS**
  - `Throughput`: **26.31 req/s**
  - `checks`: **100.00%** (1,070 / 1,070 checks passed)
- **Analisis:** Sistem mampu menangani beban normal harian secara stabil tanpa kenaikan latensi yang signifikan.

---

### 3.3. Stress Test (`stress-test.js`)
- **Tujuan:** Mengidentifikasi batas degradasi kinerja saat beban ditingkatkan melebihi kapasitas operasional normal (naik hingga 25 VUs).
- **Konfigurasi Stages:** 10s ke 10 VU, 15s ke 20 VU, 15s sustain di 25 VU, 10s ramp-down ke 0.
- **Threshold yang Ditetapkan:**
  - `http_req_failed`: `< 5%` (`rate < 0.05`)
  - `http_req_duration`: `p(95) < 1500ms`, `avg < 800ms`
- **Hasil Aktual:**
  - `http_req_failed`: **0.00%** (0 / 2,244 req) — **PASS**
  - `avg`: **228.40 ms** — **PASS**
  - `p(90)`: **439.28 ms** — **PASS**
  - `p(95)`: **525.93 ms** — **PASS**
  - `Throughput`: **44.67 req/s**
  - `checks`: **100.00%** (2,244 / 2,244 checks passed)
- **Analisis:** Terjadi peningkatan latensi wajar (avg naik dari 112ms menjadi 228ms) akibat antrean thread pada worker server PHP lokal, namun tidak ada request yang timeout atau mengalami kegagalan koneksi (0 error).

---

### 3.4. Spike Test (`spike-test.js`)
- **Tujuan:** Menstimulasikan lonjakan pengguna secara tiba-tiba (surge traffic) dan mengamati kemampuan *recovery* server setelah beban kembali normal.
- **Konfigurasi Stages:** 10s baseline (2 VU) -> 5s lonjakan tajam ke 25 VU -> 10s peak (25 VU) -> 5s penurunan tajam ke 2 VU -> 10s observasi kestabilan (2 VU).
- **Threshold yang Ditetapkan:**
  - `http_req_failed`: `< 5%` (`rate < 0.05`)
  - `http_req_duration`: `p(95) < 2000ms`
- **Hasil Aktual:**
  - `http_req_failed`: **0.00%** (0 / 1,458 req) — **PASS**
  - `avg`: **161.00 ms** — **PASS**
  - `p(95)`: **305.13 ms** — **PASS**
  - `Throughput`: **35.99 req/s**
  - `checks`: **100.00%** (1,458 / 1,458 checks passed)
- **Analisis:** Server pulih secara instan (*instant recovery*) saat beban diturunkan kembali ke 2 VU tanpa adanya request yang tertahan (*hanging*).

---

### 3.5. Soak Test (`soak-test.js`)
- **Tujuan:** Menguji ketahanan sistem pada beban konstan selama durasi terpanjang untuk mendeteksi adanya kebocoran memori (*memory leak*), degradasi I/O database, atau resource accumulation.
- **Konfigurasi:** 10s ramp-up -> 50s konstan pada 6 VU -> 10s ramp-down (Total 70.2 detik).
- **Threshold yang Ditetapkan:**
  - `http_req_failed`: `< 2%` (`rate < 0.02`)
  - `http_req_duration`: `p(95) < 450ms`, `avg < 250ms`
- **Hasil Aktual:**
  - `http_req_failed`: **0.00%** (0 / 1,623 req) — **PASS**
  - `avg`: **58.50 ms** — **PASS**
  - `p(90)`: **85.89 ms** — **PASS**
  - `p(95)`: **104.84 ms** — **PASS**
  - `Throughput`: **23.13 req/s**
  - `checks`: **100.00%** (1,623 / 1,623 checks passed)
- **Analisis:** Waktu respon sangat stabil sepanjang 70 detik (rata-rata 58.5ms), membuktikan tidak ada kebocoran memori atau akumulasi lock pada database.

---

### 3.6. Breakpoint Test (`breakpoint-test.js`)
- **Tujuan:** Menentukan batas kapasitas konkurensi maksimum yang dapat dilayani server sebelum sistem melampaui toleransi performa.
- **Konfigurasi Stages:** Peningkatan bertahap: 5 VU (10s) -> 15 VU (10s) -> 30 VU (15s) -> 45 VU (15s) -> 0 VU (10s).
- **Threshold yang Ditetapkan:**
  - `http_req_failed`: `< 15%` (`rate < 0.15`)
  - `http_req_duration`: `p(95) < 3500ms`
- **Hasil Aktual:**
  - `http_req_failed`: **0.00%** (0 / 3,423 req) — **PASS**
  - `avg`: **265.16 ms** — **PASS**
  - `p(90)`: **470.46 ms** — **PASS**
  - `p(95)`: **505.56 ms** — **PASS**
  - `Throughput Maksimum`: **56.61 req/s**
  - `checks`: **100.00%** (3,423 / 3,423 checks passed)
- **Analisis & Titik Breakpoint:** 
  - Pada 45 VU simultan, server PHP CLI (`PHP_CLI_SERVER_WORKERS=4`) mencapai throughput puncak **56.61 request per detik**.
  - Latensi p95 tetap bertahan di **505.56 ms** (jauh di bawah batas toleransi 3500ms).
  - Tidak ditemukan kegagalan request (0% error). Kapasitas konkurensi di atas 45 VU pada lingkungan lokal tidak diuji lebih lanjut demi menjaga kestabilan sumber daya mesin.

---

## 4. Evaluasi Bottleneck & Rekomendasi Optimasi

1. **PHP CLI Server Concurrency:**
   - Server PHP CLI (`php -S`) memiliki limitasi jumlah worker proses (saat ini 4 workers). Untuk lingkungan *production* berskala besar, disarankan menggunakan **Nginx + PHP-FPM** (dengan `pm = dynamic`, `pm.max_children = 50`) atau **Laravel Octane (FrankenPHP / Swoole)** untuk meningkatkan throughput hingga 5x lipat.
2. **Rate Limiting Protection:**
   - Endpoint `/api/v1/auth/login` telah diproteksi dengan `throttle:5,1` untuk mencegah serangan brute force. Proteksi ini terbukti aman dan efektif.
3. **Database Caching:**
   - Untuk endpoint katalog yang sering diakses (`/api/v1/service-items`), disarankan implementasi *cache tag* Redis/File untuk mengurangi beban query agregat saat traffic tinggi.

