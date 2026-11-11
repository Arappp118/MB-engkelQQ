# LAPORAN KONTRIBUSI AKUMULATIF ANGGOTA TIM (MINGGU 7 – MINGGU 10)
# Proyek Pengembangan Sistem MotoCare (MB-engkelQQ)

**Mata Kuliah:** Pemrograman Web  
**Semester / Kelas:** Semester 5 / Senin  
**Tahun Akademik:** 2026/2027  
**Status Evaluasi:** **100% SELESAI & TERVERIFIKASI**

---

## 1. Ringkasan Eksekutif Kontribusi Tim

Pengembangan proyek MotoCare dilaksanakan secara kolaboratif oleh 5 mahasiswa dengan pembagian tugas yang proporsional dan spesifik sepanjang 4 tahapan proyek:
- **Minggu 7:** System Design (Activity, Sequence, Use Case, UI/UX Mockup, ERD Database)
- **Minggu 8:** Core Development & Integration (Backend, Frontend Blade, Sanctum API, Role RBAC)
- **Minggu 9:** QA/QC, Security Audit, Debugging, Automated Tests (340 Tests Suite)
- **Minggu 10:** Final Performance Testing (Grafana k6), Final QA/QC, Retesting, Dokumentasi Akhir, & Persiapan Presentasi

---

## 2. Rincian Kontribusi Individual Setiap Anggota

### 1. Ridho Ramadani E. (NIM: 2401020121)
* **Peran Utama:** Backend Architecture & Security Lead Engineer
* **Rincian Kontribusi per Minggu:**
  - **Minggu 7:** Merancang skema basis data relasional (15 tabel migrasi), indexing database, dan pemodelan relasi Eloquent ORM.
  - **Minggu 8:** Mengimplementasikan service layer inti (`MechanicAssignmentService`, `PaymentService`, `DeliveryTaskService`), middleware otorisasi multi-role, dan integrasi Laravel Sanctum.
  - **Minggu 9:** Memimpin audit keamanan backend, memitigasi celah Mass Assignment, menerapkan *pessimistic row locking* (`lockForUpdate()`), dan menyusun pengujian keamanan API (`SecurityAuditApiTest`).
  - **Minggu 10:** Melakukan final security audit, memvalidasi 45 rute API v1, menganalisis data metrik performa k6, dan merumuskan rekomendasi optimasi backend produksi.
* **Artefak yang Dihasilkan:** `app/Services/*`, `database/migrations/*`, `docs/SECURITY_AUDIT.md`, `docs/final-testing/K6_PERFORMANCE_TEST_REPORT.md` (Analisis Teknis).
* **Persentase Kontribusi:** **20% (Sangat Baik / Tuntas)**

---

### 2. Muhammad Agus Farhan Talib (NIM: 2401020111)
* **Peran Utama:** Frontend Integration & Quality Assurance Lead
* **Rincian Kontribusi per Minggu:**
  - **Minggu 7:** Merancang alur interaksi pengguna (*Activity Diagram*) dan spesifikasi form input pemesanan servis bengkel.
  - **Minggu 8:** Mengembangkan integrasi antarmuka Blade dengan controller, menangani form submission, flash session message, dan validasi error visual di sisi klien.
  - **Minggu 9:** Melakukan QA/QC frontend fungsional, memverifikasi konsistensi pengiriman data form pemesanan dan bukti pembayaran, serta menyusun automated feature test untuk controller web.
  - **Minggu 10:** Melakukan final frontend QA/QC, memverifikasi kelancaran alur navigasi 4 role, dan menyiapkan skenario demonstrasi interaktif untuk presentasi dosen.
* **Artefak yang Dihasilkan:** `resources/views/bookings/*`, `resources/views/payments/*`, `docs/final-testing/FUNCTIONAL_TEST_CASES.md`.
* **Persentase Kontribusi:** **20% (Sangat Baik / Tuntas)**

---

### 3. Firman Dwi Syahputra (NIM: 2401020112)
* **Peran Utama:** UI/UX Design & Multi-Role Dashboard Specialist
* **Rincian Kontribusi per Minggu:**
  - **Minggu 7:** Merancang wireframe, desain antarmuka responsif, dan pemilihan sistem tipografi serta palette warna Tailwind CSS.
  - **Minggu 8:** Mengimplementasikan tampilan antarmuka Blade untuk 4 dashboard berbeda (Customer, Mekanik, Kurir, dan Admin), komponen navigasi responsif, dan layout tabel data transaksi.
  - **Minggu 9:** Melakukan audit tampilan responsif pada resolusi desktop, tablet, dan smartphone; memperbaiki *layout clipping* dan keselarasan tombol aksi transaksi.
  - **Minggu 10:** Melakukan final UI/UX QA/QC, memverifikasi keseragaman visual seluruh form dan modal dialog, serta menyusun materi desain untuk slide presentasi.
* **Artefak yang Dihasilkan:** `resources/views/layouts/*`, `resources/views/admin/*`, `resources/views/components/*`, `docs/SLIDE_PRESENTASI_FINAL.md`.
* **Persentase Kontribusi:** **20% (Sangat Baik / Tuntas)**

---

### 4. Farrel Abhista Farouk (NIM: 2401020117)
* **Peran Utama:** System Integration & REST API Testing Specialist
* **Rincian Kontribusi per Minggu:**
  - **Minggu 7:** Menyusun *Sequence Diagram* untuk seluruh alur transaksi bengkel: booking, konfirmasi admin, penugasan teknisi, servis, pembayaran, invoice, dan antar-jemput.
  - **Minggu 8:** Mengintegrasikan alur bisnis antara modul kendaraan, booking, servis, pembayaran, dan delivery task agar saling terhubung tanpa inkonsistensi status.
  - **Minggu 9:** Mengembangkan *End-to-End Business Flow Test* (`EndToEndBusinessFlowTest.php`) dan memetakan kepatuhan kontrak seluruh 45 endpoint REST API.
  - **Minggu 10:** Melakukan pengujian integrasi final alur lengkap, memvalidasi kepatuhan respons status code semantik (200, 201, 401, 403, 404, 422, 429), dan menyusun dokumentasi detail API test cases.
* **Artefak yang Dihasilkan:** `tests/Feature/EndToEndBusinessFlowTest.php`, `docs/API_ENDPOINT_COVERAGE.md`, `docs/final-testing/API_TEST_CASES.md`.
* **Persentase Kontribusi:** **20% (Sangat Baik / Tuntas)**

---

### 5. Saif Alif Abyan (NIM: 2401020163)
* **Peran Utama:** Lead QA/QC Coordinator & Performance Testing Engineer
* **Rincian Kontribusi per Minggu:**
  - **Minggu 7:** Memetakan *Use Case Diagram*, mendefinisikan boundary sistem, dan merancang kriteria penerimaan pengujian (*acceptance criteria*).
  - **Minggu 8:** Mengembangkan unit testing awal, konfigurasi lingkungan testing terisolasi (SQLite in-memory), dan seeder pengujian.
  - **Minggu 9:** Mengoordinasikan pelaksanaan QA/QC menyeluruh, memantau cakupan 340 test cases automated, dan mengelola pelaporan bug sistem (*Bug Tracking*).
  - **Minggu 10:** Merancang dan mengeksekusi 6 skenario pengujian beban tinggi dengan Grafana k6 (Smoke, Load, Stress, Spike, Soak, Breakpoint), mengelola retesting, menyusun laporan eksekusi kronologis, dan memfinalisasi ringkasan presentasi tim.
* **Artefak yang Dihasilkan:** `docs/final-testing/k6/*.js`, `docs/final-testing/results/*/`, `docs/final-testing/FINAL_QA_QC_REPORT.md`, `docs/final-testing/RETEST_REPORT.md`, `docs/final-testing/README_FINAL_TESTING.md`.
* **Persentase Kontribusi:** **20% (Sangat Baik / Tuntas)**

---

## 3. Matriks Evaluasi Kontribusi Akhir

| No | Nama Mahasiswa | NIM | Peran Utama | M7 | M8 | M9 | M10 | Status Akhir |
|:---:|---|:---:|---|:---:|:---:|:---:|:---:|:---:|
| 1 | **Ridho Ramadani E.** | 2401020121 | Backend & Security Lead | 100% | 100% | 100% | 100% | **LENGKAP** |
| 2 | **Muhammad Agus Farhan Talib** | 2401020111 | Frontend QA/QC Lead | 100% | 100% | 100% | 100% | **LENGKAP** |
| 3 | **Firman Dwi Syahputra** | 2401020112 | UI/UX & Dashboard Lead | 100% | 100% | 100% | 100% | **LENGKAP** |
| 4 | **Farrel Abhista Farouk** | 2401020117 | Integration & API Lead | 100% | 100% | 100% | 100% | **LENGKAP** |
| 5 | **Saif Alif Abyan** | 2401020163 | QA Lead & k6 Performance | 100% | 100% | 100% | 100% | **LENGKAP** |

**Kesimpulan Evaluasi:** Seluruh anggota kelompok telah berpartisipasi aktif, saling melengkapi, dan menyelesaikan tanggung jawabnya secara profesional dengan hasil kerja terbukti dan terverifikasi.

