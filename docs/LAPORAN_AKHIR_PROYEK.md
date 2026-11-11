# LAPORAN AKHIR PROYEK PENGEMBANGAN SISTEM INFORMASI PERBENGKELAN
# "MotoCare (MB-engkelQQ)"

**Mata Kuliah:** Pemrograman Web  
**Semester / Kelas:** Semester 5 / Senin  
**Tahun Akademik:** 2026/2027  
**Status Proyek:** **100% FINAL — VERIFIED & TESTED**

---

## DAFTAR ANGGOTA KELOMPOK

1. **Ridho Ramadani E.** — NIM: `2401020121` *(Backend & Security Lead Auditor)*
2. **Muhammad Agus Farhan Talib** — NIM: `2401020111` *(Frontend Integration & QA/QC Specialist)*
3. **Firman Dwi Syahputra** — NIM: `2401020112` *(UI/UX & Responsive Quality Control Lead)*
4. **Farrel Abhista Farouk** — NIM: `2401020117` *(Integration Flow & API Testing Specialist)*
5. **Saif Alif Abyan** — NIM: `2401020163` *(Lead QA/QC Coordinator & Performance Testing Engineer)*

---

## BAB I — PENDAHULUAN

### 1.1 Latar Belakang
Industri perbengkelan sepeda motor modern membutuhkan efisiensi tinggi dalam manajemen antrean, penugasan teknisi, transparansi pemakaian suku cadang, dan pencatatan transaksi finansial. Banyak bengkel konvensional masih mengandalkan pencatatan nota manual yang rentan terhadap manipulasi harga (*price tampering*), antrean mekanik yang tidak merata (*workload imbalance*), serta ketiadaan fasilitas antar-jemput kendaraan (*pickup & delivery*) yang transparan.

Proyek **MotoCare (MB-engkelQQ)** dikembangkan sebagai solusi sistem informasi perbengkelan komprehensif berbasis web dan REST API yang menghubungkan empat entitas operasional bengkel: **Pelanggan (Customer)**, **Teknisi (Mekanik)**, **Kurir Penjemputan (Courier)**, dan **Manajemen Bengkel (Admin)**.

### 1.2 Tujuan Pengembangan
1. Membangun sistem perbengkelan terintegrasi dengan antarmuka web modern (*Server-Side Rendered Blade & Tailwind CSS*) dan RESTful API v1 terproteksi token (*Laravel Sanctum*).
2. Menerapkan algoritma penugasan otomatis mekanik (*Auto-Assignment Service*) berbasis spesialisasi keahlian teknisi dan beban antrean terendah (*lowest active workload*).
3. Mengamankan integritas finansial transaksi melalui mekanisme *Price Snapshot*, perhitungan ulang subtotal sisi server (*server-side price verification*), dan *pessimistic row locking* untuk mencegah *race condition*.
4. Menyediakan layanan antar-jemput kendaraan terpadu (*Pickup & Delivery Task*) dengan kalkulasi tarif dinamis dan snapshot ongkos kirim.
5. Membuktikan keandalan, keamanan, dan skalabilitas sistem melalui *automated test suite* (340 Feature Tests) dan pengujian beban tinggi menggunakan **Grafana k6** (9,958 requests, 0% failure rate).

---

## BAB II — TAHAPAN PENGEMBANGAN SISTEM (ROADMAP M7 – M10)

Pengembangan sistem diselesaikan secara bertahap selama empat minggu perkuliahan:

### 2.1 Minggu 7: System Design & Architecture
- Pemodelan sistem: Use Case Diagram, Activity Diagram, dan Sequence Diagram untuk alur pemesanan, pengerjaan servis, pembayaran, dan pengantaran.
- Perancangan skema basis data relasional ternormalisasi (15 tabel migrasi).
- Perancangan antarmuka pengguna (UI/UX) responsif untuk 4 peran pengguna.

### 2.2 Minggu 8: Development & Core System Integration
- Implementasi backend berbasis Laravel 11 dan basis data MariaDB/SQLite.
- Implementasi autentikasi Laravel Breeze (Session) dan Laravel Sanctum (Bearer Token).
- Pengembangan antarmuka multi-dashboard (Customer, Mekanik, Kurir, Admin).
- Integrasi alur bisnis: Booking -> Auto-assignment -> Servis -> Invoice -> Pembayaran -> Delivery.

### 2.3 Minggu 9: Audit, Debugging, QA/QC, & API Testing
- Audit keamanan menyeluruh terhadap potensi kerentanan IDOR, Mass Assignment, dan CSRF.
- Penegakan integritas data: *price snapshot* transaksi dan *pessimistic locking* (`lockForUpdate()`).
- Penyusunan 340 Feature & Unit Tests otomatis yang mencakup 100% rute aplikasi.
- Pembuktian kepatuhan kontrak pada 45 endpoint REST API v1.

### 2.4 Minggu 10: Final Performance Testing, Final QA/QC, & Documentation
- Pengujian performa beban tinggi (*High Concurrency Load Testing*) menggunakan **Grafana k6** dengan 6 skenario: Smoke, Load, Stress, Spike, Soak, dan Breakpoint Testing.
- Pengujian kapasitas puncak hingga 45 Virtual Users simultan (throughput mencapai 56.61 req/detik).
- Penyusunan laporan akhir, matriks QA/QC, test execution log, laporan kontribusi tim, dan materi presentasi dosen.

---

## BAB III — ARSITEKTUR & FITUR UNGGULAN SISTEM

### 3.1 Arsitektur Multi-Peran (RBAC)
Sistem memisahkan otorisasi menjadi 4 hak akses ketat:
1. **Customer:** Mendaftarkan kendaraan, mengajukan booking servis biasa / antar-jemput, memantau progres servis secara real-time, melihat invoice resmi, dan mengunggah bukti bayar.
2. **Mekanik:** Menerima penugasan servis otomatis sesuai keahlian (contoh: spesialisasi 4-tak / matic), mencatat diagnosis kerusakan, menambahkan suku cadang dan jasa, serta menyelesaikan pengerjaan.
3. **Kurir:** Menerima tugas penjemputan kendaraan customer, mengupdate status perjalanan (*in-transit*), menyelesaikan penjemputan ke bengkel, dan mengantarkan kembali motor yang selesai diservis.
4. **Admin:** Mengonfirmasi booking, memantau antrean bengkel, mengelola staf & mekanik, memverifikasi atau menolak bukti pembayaran, mengatur master harga & stok suku cadang, serta mengonfigurasi rekening bank/QRIS.

### 3.2 Fitur Unggulan Rekayasa Perangkat Lunak
1. **Algoritma Auto-Assignment Mekanik:**
   Ketika booking dikonfirmasi atau penjemputan selesai, sistem secara otomatis mengevaluasi mekanik aktif yang memiliki spesialisasi sesuai tipe mesin kendaraan (`4_tak`, `matic`, dll) dan memilih mekanik dengan jumlah antrean tugas aktif paling sedikit.
2. **Proteksi Integritas Finansial (Price Snapshot):**
   Harga suku cadang dan jasa dikunci ke dalam tabel `service_order_items` pada detik transaksi ditambahkan. Perubahan harga pada master katalog di masa depan tidak akan pernah mengubah tagihan invoice yang telah terbit.
3. **Pessimistic Row Locking pada Pembayaran:**
   Verifikasi mutasi rekening dan persetujuan transaksi menggunakan `lockForUpdate()` dalam transaksi database atomik untuk mencegah *double-spending* atau *race condition*.
4. **Proteksi Brute-Force Rate Limiting:**
   Endpoint login publik diamankan dengan middleware `throttle:5,1` yang membatasi maksimal 5 percobaan login per menit.

---

## BAB IV — HASIL PENGUJIAN & VALIDASI (100% PASS)

### 4.1 Automated Feature & Regression Testing (PHPUnit)
- **Total Test Cases:** **340 Tests** (801 Assertions)
- **Tingkat Kelulusan:** **100% PASS (0 Failed, 0 Error)**
- **Waktu Eksekusi:** **31.02 detik**

### 4.2 Cakupan REST API v1
- **Total Endpoint Aktif:** **45 Endpoints**
- **Status Verifikasi:** **45/45 Teruji & Valid (100% PASS)**
- **Standar Respons:** Kontrak format konsisten `{"success": true/false, "data": ..., "message": "..."}` dengan kode HTTP semantik (200, 201, 401, 403, 404, 422, 429).

### 4.3 Pengujian Beban Grafana k6 (6 Skenario Pengujian)

| Skenario Pengujian | Konfigurasi Beban | Durasi | Total Requests | Throughput | Latensi p95 | Error Rate | Status |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **Smoke Test** | 2 VU konstan | 15.1s | 140 | 9.27 req/s | 82.26 ms | 0.00% | **PASS** |
| **Load Test** | 10 VU (3 stages) | 40.7s | 1,070 | 26.31 req/s | 306.04 ms | 0.00% | **PASS** |
| **Stress Test** | 25 VU (4 stages) | 50.2s | 2,244 | 44.67 req/s | 525.93 ms | 0.00% | **PASS** |
| **Spike Test** | 2 -> 25 -> 2 VU | 40.5s | 1,458 | 35.99 req/s | 305.13 ms | 0.00% | **PASS** |
| **Soak Test** | 6 VU konstan | 70.2s | 1,623 | 23.13 req/s | 104.84 ms | 0.00% | **PASS** |
| **Breakpoint Test** | 5 -> 15 -> 30 -> 45 VU | 60.5s | 3,423 | 56.61 req/s | 505.56 ms | 0.00% | **PASS** |
| **TOTAL AKUMULASI** | — | **277.2s** | **9,958** | **35.92 req/s** | — | **0.00%** | **ALL PASS** |

---

## BAB V — KESIMPULAN & REKOMENDASI

### 5.1 Kesimpulan
Sistem informasi perbengkelan **MotoCare (MB-engkelQQ)** telah diselesaikan secara tuntas (**100% Final**) dan memenuhi seluruh kriteria akademik mata kuliah Pemrograman Web. Seluruh modul bisnis, mekanisme keamanan, hak akses multi-peran, dan REST API v1 telah terverifikasi melalui 340 feature tests dan 9,958 pengujian beban k6 tanpa kegagalan.

### 5.2 Status Kesiapan Presentasi
**STATUS: READY FOR PRESENTATION**  
Sistem berada dalam kondisi prima, stabil, dan siap didemonstrasikan secara interaktif di hadapan dosen penguji.

