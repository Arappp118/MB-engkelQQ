# Laporan Pembagian Kontribusi Anggota Tim (Minggu 10) — MotoCare (MB-engkelQQ)
## Tahap Final Testing, Final QA/QC, Audit, dan Dokumentasi Presentasi

Dokumen ini memetakan pembagian tugas, tanggung jawab spesifik, dan bukti hasil kerja nyata untuk ke-5 anggota tim pada tahap penyelesaian proyek Minggu 10.

---

## 1. Rincian Kontribusi Anggota Tim

### 1. Ridho Ramadani E. (NIM: 2401020121)
- **Peran & Tanggung Jawab:** Backend & Security Lead Auditor
- **Ruang Lingkup Tugas:**
  - Melakukan audit keamanan menyeluruh terhadap arsitektur backend Laravel 11 (`config/`, middleware, route guards).
  - Memeriksa keabsahan 45 rute REST API v1 dan otorisasi role berbasis Laravel Sanctum.
  - Memverifikasi konfigurasi environment (`.env`, `.env.example`, database connection) tanpa mengekspos kredensial rahasia.
  - Memvalidasi data metrik performa k6, menganalisis profil error API, dan menyusun rekomendasi teknis optimasi backend (caching, database indexing, FPM worker concurrency).
- **Bukti Artefak Pelaksanaan:**
  - Evaluasi keamanan API pada `docs/final-testing/FINAL_QA_QC_REPORT.md` (Aspek 1, 14, 16).
  - Analisis performa dan bottleneck pada `docs/final-testing/K6_PERFORMANCE_TEST_REPORT.md` (Bagian 4).

---

### 2. Muhammad Agus Farhan Talib (NIM: 2401020111)
- **Peran & Tanggung Jawab:** Frontend Integration & QA/QC Specialist
- **Ruang Lingkup Tugas:**
  - Menguji integrasi antara formulir frontend (Blade template) dengan backend controller dan REST API.
  - Memverifikasi alur navigasi pengguna, handling flash message/session notification, dan validasi form client-side.
  - Memastikan kelancaran seluruh skenario demonstrasi live (*interactive user journey*) agar siap didemonstrasikan di hadapan dosen penguji.
- **Bukti Artefak Pelaksanaan:**
  - Pengujian integrasi modul pada `docs/final-testing/FINAL_QA_QC_REPORT.md` (Aspek 11, 12, 13).
  - Verifikasi alur fungsional pada `docs/final-testing/FUNCTIONAL_TEST_CASES.md` (Alur 1, 2, 3, 4).

---

### 3. Firman Dwi Syahputra (NIM: 2401020112)
- **Peran & Tanggung Jawab:** UI/UX & Responsive Quality Control Lead
- **Ruang Lingkup Tugas:**
  - Memeriksa konsistensi visual dan antarmuka pada seluruh dashboard role (Customer, Mekanik, Kurir, dan Admin).
  - Melakukan audit responsivitas tampilan (desktop, tablet, mobile viewport) menggunakan utilitas Tailwind CSS.
  - Memverifikasi kejelasan elemen interaktif (tombol aksi status transaksi, modal upload bukti transfer, pratinjau invoice tagihan).
- **Bukti Artefak Pelaksanaan:**
  - Matriks audit UI/UX pada `docs/final-testing/FINAL_QA_QC_REPORT.md` (Aspek 11 - UI/UX & Responsive Layout).
  - Verifikasi keselarasan visual pada `docs/final-testing/PRESENTATION_TESTING_SUMMARY.md`.

---

### 4. Farrel Abhista Farouk (NIM: 2401020117)
- **Peran & Tanggung Jawab:** Integration Flow & API Testing Specialist
- **Ruang Lingkup Tugas:**
  - Melakukan pengujian integrasi alur bisnis berbasis *Sequence Diagram* yang telah dirancang pada Minggu 7.
  - Memeriksa siklus hidup transaksi lengkap: booking -> mechanic auto-assignment -> diagnosis & sparepart -> payment -> invoice -> delivery -> admin verification.
  - Mendokumentasikan matriks cakupan dan status keberhasilan 45 endpoint REST API v1.
- **Bukti Artefak Pelaksanaan:**
  - Penyusunan matriks 45 rute pada `docs/final-testing/API_TEST_CASES.md`.
  - Verifikasi skenario alur transaksi pada `docs/final-testing/FUNCTIONAL_TEST_CASES.md`.

---

### 5. Saif Alif Abyan (NIM: 2401020163)
- **Peran & Tanggung Jawab:** Lead QA/QC Coordinator & Performance Testing Engineer
- **Ruang Lingkup Tugas:**
  - Mengoordinasikan seluruh tahapan Final QA/QC, penyusunan test case, dan penataan struktur folder dokumentasi final.
  - Merancang, mengonfigurasi, dan mengeksekusi 6 skenario pengujian beban k6 (Smoke, Load, Stress, Spike, Soak, Breakpoint Testing).
  - Mengelola bug management, eksekusi retesting skema database, verifikasi 340 automated tests, dan penyusunan ringkasan kesiapan presentasi.
- **Bukti Artefak Pelaksanaan:**
  - Skrip pengujian k6 pada `docs/final-testing/k6/*.js`.
  - Artefak hasil eksekusi pada `docs/final-testing/results/*/` (JSON summary & output log).
  - Laporan bug & retest pada `docs/final-testing/BUG_REPORT.md` dan `docs/final-testing/RETEST_REPORT.md`.
  - Ringkasan presentasi pada `docs/final-testing/PRESENTATION_TESTING_SUMMARY.md`.

---

## 2. Tabel Matriks Kontribusi Tim

| Nama Anggota | NIM | Fokus Tanggung Jawab | Artefak Utama yang Dihasilkan | Status Verifikasi |
|---|:---:|---|---|:---:|
| **Ridho Ramadani E.** | 2401020121 | Backend Audit, API Security, Environment Config | `FINAL_QA_QC_REPORT.md`, `K6_PERFORMANCE_TEST_REPORT.md` | **SELESAI (100%)** |
| **M. Agus Farhan Talib** | 2401020111 | Frontend QA/QC, Form Integration, Demo Prep | `FUNCTIONAL_TEST_CASES.md`, `FINAL_QA_QC_REPORT.md` | **SELESAI (100%)** |
| **Firman Dwi Syahputra**| 2401020112 | UI/UX Consistency, Multi-Role Dashboard, Responsive | `FINAL_QA_QC_REPORT.md`, `PRESENTATION_TESTING_SUMMARY.md` | **SELESAI (100%)** |
| **Farrel Abhista Farouk** | 2401020117 | Sequence Flow Integration, 45 API Coverage | `API_TEST_CASES.md`, `FUNCTIONAL_TEST_CASES.md` | **SELESAI (100%)** |
| **Saif Alif Abyan** | 2401020163 | QA Lead, k6 Performance Suite, Bug Management | `k6/*.js`, `results/*/`, `BUG_REPORT.md`, `RETEST_REPORT.md` | **SELESAI (100%)** |

