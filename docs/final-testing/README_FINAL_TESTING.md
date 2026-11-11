# Dokumentasi Pengujian Akhir (Final Testing) — MotoCare (MB-engkelQQ)
## Pemrograman Web — Minggu 10: Final Performance Testing, Final QA/QC, Audit, & Presentation Prep

Dokumentasi ini merupakan laporan komprehensif pengujian tahap akhir untuk sistem web bengkel terintegrasi **MotoCare (MB-engkelQQ)**, mencakup audit arsitektur backend/frontend, verifikasi seluruh endpoint REST API, pengujian fungsional dan alur bisnis end-to-end, pengujian performa beban tinggi dengan Grafana k6, manajemen bug, retesting, dan pembagian kontribusi tim.

---

## 1. Daftar Dokumen Final Testing

Seluruh laporan dan artefak pengujian disimpan secara terstruktur di dalam folder `docs/final-testing/`:

```
docs/final-testing/
├── README_FINAL_TESTING.md             # Panduan induk dan ringkasan eksekutif pengujian
├── FINAL_QA_QC_REPORT.md               # Laporan audit QA/QC komprehensif seluruh modul sistem
├── K6_PERFORMANCE_TEST_REPORT.md       # Laporan analisis pengujian performa k6 (Smoke, Load, Stress, Spike, Soak, Breakpoint)
├── API_TEST_CASES.md                   # Matriks pengujian 45 endpoint REST API v1
├── FUNCTIONAL_TEST_CASES.md            # Skenario pengujian fungsional modul bisnis end-to-end
├── BUG_REPORT.md                       # Daftar temuan bug, klasifikasi severity/priority, dan status resolusi
├── RETEST_REPORT.md                    # Laporan pengujian ulang (retest) dan verifikasi regresi (340 feature tests)
├── TEST_EXECUTION_LOG.md               # Catatan kronologis eksekusi pengujian otomatis dan performa
├── CONTRIBUTION_WEEK10.md              # Rincian kontribusi dan tanggung jawab 5 anggota tim pada Minggu 10
├── PRESENTATION_TESTING_SUMMARY.md     # Ringkasan eksekutif untuk materi presentasi dan demo dosen
├── k6/                                 # Skrip pengujian k6
│   ├── config.example.js
│   ├── smoke-test.js
│   ├── load-test.js
│   ├── stress-test.js
│   ├── spike-test.js
│   ├── soak-test.js
│   ├── breakpoint-test.js
│   └── README_K6.md
└── results/                            # Bukti artefak keluaran pengujian k6 (output log & JSON summary)
    ├── smoke/
    ├── load/
    ├── stress/
    ├── spike/
    ├── soak/
    └── breakpoint/
```

---

## 2. Ringkasan Eksekutif Hasil Pengujian

| Komponen Pengujian | Metrik / Target | Hasil Aktual | Status |
|---|---|---|:---:|
| **Automated PHPUnit Tests** | 340 Feature & Unit Tests | 340 Passed, 0 Failed (801 Assertions) | **PASS** |
| **REST API v1 Coverage** | 45 Endpoints terdaftar | 45 Endpoints teruji & tervasilitasi | **PASS** |
| **k6 Smoke Test** | 2 VUs, durasi 15s | 140 reqs, error 0.00%, p95 82.26ms | **PASS** |
| **k6 Load Test** | 10 VUs, durasi 40s | 1,070 reqs, error 0.00%, p95 306.04ms | **PASS** |
| **k6 Stress Test** | 25 VUs, durasi 50s | 2,244 reqs, error 0.00%, p95 525.93ms | **PASS** |
| **k6 Spike Test** | 2 -> 25 -> 2 VUs, 40s | 1,458 reqs, error 0.00%, p95 305.13ms | **PASS** |
| **k6 Soak Test** | 6 VUs konstan, 70s | 1,623 reqs, error 0.00%, p95 104.84ms | **PASS** |
| **k6 Breakpoint Test** | 5 -> 15 -> 30 -> 45 VUs, 60s | 3,423 reqs, error 0.00%, p95 505.56ms, 56.6 req/s | **PASS** |
| **Total Request k6 Teruji** | Concurrency test suite | **9,958 requests**, **0 failure (0.00% error rate)** | **PASS** |
| **Kesiapan Presentasi** | Demonstrasi live & presentasi | **READY FOR PRESENTATION** | **READY** |

---

## 3. Lingkungan Uji & Kebijakan Keamanan

- **Framework:** Laravel 11.56.1 (PHP 8.4.27 CLI)
- **Database Mesin Uji:** SQLite (`motocare`) terisolasi dengan data uji seeder.
- **k6 Version:** `v0.54.0` (linux/amd64).
- **Proteksi Kredensial:** Seluruh token otentikasi diinjeksi via environment variables (`CUSTOMER_TOKEN`, `ADMIN_TOKEN`). Tidak ada credential, secret, atau token yang di-hardcode ke file laporan.

