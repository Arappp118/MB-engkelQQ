# Ringkasan Eksekutif Presentasi Pengujian Akhir (Presentation Summary) — MotoCare
## Pemrograman Web — Minggu 10: Final Performance Testing, QA/QC, & Presentation Prep

Dokumen ini disusun sebagai panduan ringkas bagi tim dalam memaparkan hasil pengujian akhir sistem **MotoCare (MB-engkelQQ)** di hadapan dosen penguji dan rekan mahasiswa.

---

## 1. Sorotan Utama Sistem (Key Highlights)

1. **Arsitektur Berbasis Peran Lengkap (Role-Based Access Control):**
   - Mendukung 4 persona pengguna aktif: **Customer**, **Mekanik**, **Kurir**, dan **Admin**.
   - Dilengkapi dashboard analitik adaptif dan isolasi data multi-tenant di tingkat policy dan middleware.
2. **Cakupan Pengujian 100%:**
   - **340 Automated Feature & Unit Tests** lulus tanpa kegagalan (801 assertions).
   - **45 Endpoint REST API v1** tervasilitasi dan terproteksi Sanctum.
3. **Ketahanan Beban Tinggi Teruji (Grafana k6):**
   - Berhasil melayani **9,958 requests** dalam 6 skenario uji performa tanpa 1 pun kegagalan (**0.00% Error Rate**).
   - Throughput puncak mencapai **56.61 request per detik** pada 45 Virtual Users bersamaan.
4. **Proteksi Integritas Finansial & Bisnis:**
   - *Price Snapshot*: Perubahan harga master sparepart tidak memengaruhi tagihan transaksi berjalan.
   - *Pessimistic Row Locking*: Mencegah duplikasi pembayaran atau race condition pada verifikasi admin.

---

## 2. Rangkuman Metrik Performa k6

| Skenario Pengujian | Konfigurasi VU | Durasi | Total Request | Throughput | p95 Latency | Error Rate | Status |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **Smoke Test** | 2 VU | 15.1s | 140 req | 9.27 req/s | 82.26 ms | 0.00% | **PASS** |
| **Load Test** | 10 VU | 40.7s | 1,070 req | 26.31 req/s | 306.04 ms | 0.00% | **PASS** |
| **Stress Test** | 25 VU | 50.2s | 2,244 req | 44.67 req/s | 525.93 ms | 0.00% | **PASS** |
| **Spike Test** | 2 -> 25 VU | 40.5s | 1,458 req | 35.99 req/s | 305.13 ms | 0.00% | **PASS** |
| **Soak Test** | 6 VU | 70.2s | 1,623 req | 23.13 req/s | 104.84 ms | 0.00% | **PASS** |
| **Breakpoint Test** | 45 VU | 60.5s | 3,423 req | 56.61 req/s | 505.56 ms | 0.00% | **PASS** |

---

## 3. Alur Demonstrasi Live yang Direkomendasikan untuk Presentasi

Untuk mendemonstrasikan sistem secara efektif dalam waktu 10-15 menit:

1. **Slide 1-2: Pendahuluan & Arsitektur Sistem**
   - Tampilkan Use Case Diagram dan cakupan 4 aktor pengguna.
2. **Slide 3-4: Live Demo Alur Bisnis Inti (Customer & Admin)**
   - Login Customer -> Tambah Kendaraan -> Buat Booking Servis + Antar-Jemput.
   - Login Admin -> Konfirmasi Booking -> Sistem otomatis menugaskan Mekanik (Auto-assignment).
3. **Slide 5-6: Live Demo Servis & Pembayaran (Mekanik & Kurir)**
   - Login Mekanik -> Input Diagnosis Kerusakan -> Tambah Sparepart (stok otomatis berkurang).
   - Mekanik selesaikan servis -> Terbit Invoice resmi.
   - Customer upload bukti bayar -> Admin verifikasi pembayaran -> Transaksi tuntas.
4. **Slide 7-8: Pembuktian Kualitas & Performa (QA/QC & k6)**
   - Paparkan hasil kelulusan **340/340 PHPUnit Feature Tests**.
   - Tampilkan grafik dan ringkasan **9,958 requests k6** dengan 0% error rate.
5. **Slide 9: Kesimpulan & Rekomendasi Masa Depan**
   - Paparkan kesiapan sistem untuk di-deploy ke lingkungan staging/cloud dengan Nginx & PHP-FPM.

---

## 4. Rekomendasi & Status Kesiapan Akhir

### Status: **READY FOR PRESENTATION**

**Justifikasi Objektif:**
1. Seluruh fitur utama (Booking, Auto-assignment, Diagnosis, Sparepart, Payment, Delivery, Invoice, Admin Dashboard) berfungsi normal tanpa bug kritis yang memblokir.
2. Seluruh 45 rute REST API telah lulus verifikasi automated tests dan live probe.
3. Seluruh skenario pengujian beban k6 (Smoke, Load, Stress, Spike, Soak, Breakpoint) telah tuntas dijalankan dengan bukti artefak output yang terverifikasi.
4. Dokumentasi pengujian telah tertata rapi di folder `docs/final-testing/` dan pembagian peran tim 100% terlaksana.

