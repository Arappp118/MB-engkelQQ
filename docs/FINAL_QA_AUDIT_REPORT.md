# Laporan Akhir Audit QA/QC Backend — MotoCare (MB-engkelQQ)

**Repository:** [https://github.com/Arappp118/MB-engkelQQ](https://github.com/Arappp118/MB-engkelQQ)  
**Branch:** `main`  
**Commit Baseline:** `f256f50f28e520ccdec9ff13582fcf3c0e74fb0a`  
**Lingkungan:** PHP 8.4.27, Laravel 11.56.1, MariaDB 11.8.9 (Production DB: `motocare`), SQLite in-memory (`APP_ENV=testing`), Sanctum  
**Tanggal Pengujian:** 27 Oktober 2026  

---

## 1. Ringkasan Eksekutif

Audit komprehensif QA/QC Backend ini dilaksanakan untuk memverifikasi kesiapan rilis sistem backend Laravel MotoCare, kompatibilitasnya dengan aplikasi mobile Flutter (`mb_engkelqq_mobile`), integritas bisnis, serta ketahanan keamanan data.

- **Rangkaian Tes Otomatis (PHPUnit):** **340 Tests PASSED**, **801 Assertions** (0 Failed, 0 Errors, 0 Skipped).
- **Rangkaian Tes Live HTTP:** Berhasil dieksekusi secara nyata terhadap server lokal aktif (`http://127.0.0.1:8000`), mencakup endpoint Health, Autentikasi, Rate Limiting (Throttle 429), Dashboard Multi-Role, serta Akses Kontrol Terproteksi.
- **Matriks Detail Endpoint:** Rincian pemetaan endpoint API v1 beserta metode pembuktiannya dapat dilihat pada [Matriks Cakupan API](API_ENDPOINT_COVERAGE.md).
- **Integritas Database:** Terverifikasi 0 orphan record, 0 stok negatif, dan snapshot harga historis terproteksi dari mutasi harga master.
- **Status Rilis:** **CONDITIONAL PASS — READY WITH LIMITATIONS** (Keterbatasan pada gateway pihak ketiga otomatis yang memerlukan akun merchant eksternal).

---

## 2. Metrik Pengujian & Hasil Eksekusi

| Kategori Pengujian | Perintah / Metode | Hasil Aktual | Keterangan |
|---|---|:---:|---|
| **Feature & Unit Tests** | `php artisan test` | **340 Passed** | 801 assertions, durasi ~24.7 detik, exit code 0. |
| **End-to-End Business Flow** | `php artisan test tests/Feature/EndToEndBusinessFlowTest.php` | **4 Passed** | 61 assertions memvalidasi Flow 1 (Servis Biasa), Flow 2 (Antar-Jemput), Flow 3 (Pembayaran), Flow 4 (Keamanan & Integritas). |
| **API Health Probe** | `curl -s http://127.0.0.1:8000/api/v1/health` | **PASS (200)** | Format standar JSON `{"success":true,"message":"MotoCare API berjalan","data":{"version":"v1"}}`. |
| **Live Auth & Token Probe** | `curl -X POST /api/v1/auth/login` | **PASS (200)** | PlainTextToken berhasil di-issue dan dicabut saat logout. |
| **Rate Limiter Probe** | 6x login request beruntun | **PASS (429)** | Throttle 5x/menit bekerja aktif menangkal brute force (`Too Many Requests`). |
| **Cross-Role Authorization** | Probe live HTTP non-admin & antar-role | **PASS (403)** | Penolakan eksplisit via Policy / Gate Laravel. |
| **Database Orphan Check** | SQL check pada database `motocare` | **PASS (0)** | 0 broken foreign references pada tabel kendaraan, booking, order, dan pembayaran. |

---

## 3. Cakupan Pengujian Berdasarkan Modul

Rincian status per method dan endpoint dapat ditinjau pada [Matriks Cakupan API](API_ENDPOINT_COVERAGE.md).

### A. Autentikasi & Sanctum Token (`PASS`)
- Login valid menghasilkan token Sanctum terenkripsi.
- Logout berhasil mencabut (*revoke*) token personal access.
- Percobaan request dengan token yang sudah dicabut langsung ditolak dengan HTTP 401 (`Anda harus login terlebih dahulu.`).
- Proteksi brute force login aktif via middleware `throttle:5,1`.

### B. Kendaraan & Booking Servis (`PASS`)
- CRUD kendaraan terisolasi penuh pada customer pemilik.
- Pembuatan booking memvalidasi kepemilikan kendaraan dan menghasilkan kode unik transaksi.
- Transisi status booking divalidasi ketat (`canTransitionTo`), mencegah loncatan status ilegal.

### C. Service Orders & Mekanik (`PASS`)
- Penugasan mekanik otomatis (`MechanicAssignmentService`) mencocokkan spesialisasi mesin (2-tak, 4-tak, kelistrikan) dan beban kerja.
- Mekanik hanya dapat melihat dan memperbarui pesanan servis miliknya sendiri.
- Aturan `BR-012` ditegakkan: pengerjaan servis tidak dapat diselesaikan tanpa diagnosis mekanik dan minimal 1 tindakan servis / sparepart.

### D. Pembayaran & Invoice (`PASS`)
- Nominal pembayaran diambil langsung dari grand total server (`$lockedOrder->grand_total`), menolak manipulasi input form.
- Timestamp `paid_at` bernilai `NULL` saat pengajuan dan hanya terisi saat verifikasi manual oleh admin.
- Transaksi database dengan *pessimistic locking* (`lockForUpdate()`) mencegah pembayaran ganda / race condition.
- Alur penolakan pembayaran mewajibkan parameter `reason` dan mendukung pengajuan ulang bukti bayar oleh customer.

### E. Antar-Jemput (Pickup & Delivery) (`PASS`)
- Kurir hanya dapat mengakses penugasan miliknya.
- Status task tervalidasi (`assigned` -> `in_progress` -> `completed`).
- Booking servis tidak dipaksa selesai jika tugas pengantaran masih aktif (`ready_for_delivery`).

---

## 4. Evaluasi Keterbatasan & Pekerjaan Tertunda

1. **Payment Gateway Otomatis (Dynamic QRIS / Webhook):**  
   *Status:* `BLOCKED — Memerlukan Konfigurasi / Akun Merchant Provider yang Sah`.  
   Sistem mengadopsi transfer bank manual dan QRIS statis terverifikasi admin. Webhook otomatis pihak ketiga belum diintegrasikan karena ketiadaan kredensial resmi.
2. **Pengujian Live Browser Otomatis pada MariaDB Pengguna:**  
   *Status:* `BLOCKED — Dilewati demi Keamanan Data`.  
   Dilewati secara sengaja untuk mencegah mutasi data operasional lokal, diverifikasi 100% via HTTP Web Integration Tests.
