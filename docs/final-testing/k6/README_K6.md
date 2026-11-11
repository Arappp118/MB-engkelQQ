# Petunjuk Pengujian Performa k6 — MotoCare (MB-engkelQQ)

Direktori ini berisi skrip pengujian beban dan performa menggunakan [k6](https://k6.io/) untuk REST API MotoCare.

---

## 1. Prasyarat & Lingkungan Uji

- **k6 Version:** `v0.54.0` (terpasang di sistem lokal)
- **Target URL:** `http://127.0.0.1:8000` (lingkungan pengujian lokal terkendali)
- **Database:** SQLite (`motocare`) dengan data uji terisolasi.
- **PHP CLI Server Workers:** `PHP_CLI_SERVER_WORKERS=4`

---

## 2. Struktur Skrip Pengujian

| Skrip | Jenis Pengujian | Target VU | Durasi | Tujuan Utama |
|---|---|:---:|:---:|---|
| `smoke-test.js` | Smoke Testing | 2 VU | 15s | Verifikasi endpoint dasar aktif, response 200, contract check |
| `load-test.js` | Load Testing | 10 VU | 40s | Mengukur performa pada beban normal dan target terprediksi |
| `stress-test.js` | Stress Testing | 25 VU | 50s | Mengamati degradasi latency saat beban dinaikkan melebihi normal |
| `spike-test.js` | Spike Testing | 25 VU | 40s | Menguji resiliensi dan recovery terhadap lonjakan tiba-tiba |
| `soak-test.js` | Soak / Endurance | 6 VU | 70s | Menguji kestabilan memori dan latensi pada durasi lebih panjang |
| `breakpoint-test.js` | Breakpoint Testing | 45 VU | 60s | Menemukan batas kapasitas konkurensi maksimum server lokal |

---

## 3. Cara Menjalankan Uji Performa

### A. Menjalankan Server Aplikasi Lokal
Pastikan server aplikasi berjalan di port `8000`:
```bash
DB_CONNECTION=sqlite DB_DATABASE=motocare PHP_CLI_SERVER_WORKERS=4 php -S 127.0.0.1:8000 -t public
```

### B. Menyiapkan Token Otentikasi (Environment Variable)
Dapatkan bearer token untuk akun uji tanpa menuliskannya secara *hardcoded*:
```bash
export CUSTOMER_TOKEN=$(DB_CONNECTION=sqlite DB_DATABASE=motocare php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); echo \App\Models\User::where('email', 'customer@motocare.test')->first()->createToken('k6')->plainTextToken;")
export ADMIN_TOKEN=$(DB_CONNECTION=sqlite DB_DATABASE=motocare php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); echo \App\Models\User::where('email', 'admin@motocare.test')->first()->createToken('k6')->plainTextToken;")
```

### C. Menjalankan Pengujian k6
Gunakan binary k6 yang terpasang di sistem:
```bash
# Smoke Test
/home/wh4mi/go/bin/k6 run --summary-export=docs/final-testing/results/smoke/summary.json docs/final-testing/k6/smoke-test.js

# Load Test
/home/wh4mi/go/bin/k6 run --summary-export=docs/final-testing/results/load/summary.json docs/final-testing/k6/load-test.js

# Stress Test
/home/wh4mi/go/bin/k6 run --summary-export=docs/final-testing/results/stress/summary.json docs/final-testing/k6/stress-test.js

# Spike Test
/home/wh4mi/go/bin/k6 run --summary-export=docs/final-testing/results/spike/summary.json docs/final-testing/k6/spike-test.js

# Soak Test
/home/wh4mi/go/bin/k6 run --summary-export=docs/final-testing/results/soak/summary.json docs/final-testing/k6/soak-test.js

# Breakpoint Test
/home/wh4mi/go/bin/k6 run --summary-export=docs/final-testing/results/breakpoint/summary.json docs/final-testing/k6/breakpoint-test.js
```

---

## 4. Keamanan & Kebijakan Data Uji

1. **Tidak Ada Token / Password Hardcoded:** Seluruh token diinjeksikan secara dinamis via env var `CUSTOMER_TOKEN` / `ADMIN_TOKEN`.
2. **Isolasi Database:** Pengujian dijalankan terhadap database lokal terisolasi (`motocare`).
3. **Safety Throttle:** Endpoint sensitif seperti `/api/v1/auth/login` memiliki `throttle:5,1` sehingga tidak di-spam dalam load testing untuk mencegah false positive lockout.

