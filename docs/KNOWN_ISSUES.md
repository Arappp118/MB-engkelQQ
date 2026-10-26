# Daftar Isu Diketahui & Keterbatasan Eksternal (Known Issues) — MotoCare

Dokumen ini memuat isu-isu yang diketahui, keterbatasan infrastruktur eksternal, dan pertimbangan teknis untuk rilis backend MotoCare.

---

## 1. Keterbatasan Eksternal & Fitur Tertunda

### A. Otomatisasi Payment Gateway (Dynamic QRIS & Webhook Callback)
* **Status:** `BLOCKED — Memerlukan Akun Merchant / Provider Resmi`
* **Deskripsi:** Sistem saat ini tidak menyertakan SDK payment gateway komersial (seperti Midtrans, Xendit, atau DOKU) dan tidak menyimpan kredensial secret sandbox/production pada repository.
* **Solusi Aktual:** Menggunakan alur **Transfer Bank Manual & QRIS Statis Merchant dengan Verifikasi Admin**.
* **Tindakan Lanjutan:** Ketika akun merchant resmi telah terbit, integrasi webhook dapat diaktifkan menggunakan template environment variables yang telah disediakan pada [`docs/QA_PAYMENT_AUDIT.md`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/docs/QA_PAYMENT_AUDIT.md).

### B. Ekstensi PHP `intl` untuk Utilitas CLI
* **Status:** `INFORMATIONAL / ENVIRONMENT NOTICE`
* **Deskripsi:** Perintah bawaan Laravel `php artisan db:show` memunculkan notice CLI bahwa ekstensi PHP `intl` belum terpasang pada CLI untuk memformat ukuran tabel secara visual (`Number::fileSize()`).
* **Dampak:** Tidak mempengaruhi runtime aplikasi web, API, maupun transaksi database.
* **Solusi:** Jalankan `sudo apt-get install php8.4-intl` pada server production.

### C. Pembatasan Pengujian Live Browser terhadap Database Operasional
* **Status:** `BLOCKED DEMI KEAMANAN DATA`
* **Deskripsi:** Pengujian otomatisasi browser live yang membutuhkan reset basis data atau seeding massal tidak dijalankan pada database lokal MariaDB `motocare` untuk melindungi data operasional pengguna dan tim Flutter.
* **Mitigasi:** Seluruh fungsionalitas Blade dan interaksi web telah diuji tuntas melalui 340 Feature & Integration Tests di lingkungan in-memory terisolasi (`APP_ENV=testing`).

---

## 2. Isu yang Berhasil Diselesaikan pada Sesi Audit

1. **Race Condition Pembayaran:** Telah diselesaikan dengan *pessimistic row locking* (`lockForUpdate()`) di dalam transaksi database atomik.
2. **Lifecycle `paid_at`:** Telah diperbaiki sehingga `paid_at` bernilai `null` saat formulir dikirim dan hanya diisi saat admin menyetujui mutasi rekening.
3. **Pemaksaan Status Booking Selesai:** Telah diganti dengan transisi sah (`canTransitionTo`), mendukung status `ready_for_delivery` jika terdapat tugas antar-jemput aktif.
4. **Otorisasi Eksplisit Penolakan & Verifikasi Pembayaran:** Telah ditegakkan secara eksplisit di controller Web dan API.
5. **Manajemen Dinamis Rekening Bengkel & QRIS:** Tabel `payment_settings` dan UI admin telah tersedia untuk menghindari hardcoding nomor rekening pada kode sumber.
