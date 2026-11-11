# Skenario Pengujian Fungsional End-to-End — MotoCare (MB-engkelQQ)
## Tahap Final Testing — Pemrograman Web Minggu 10

Dokumen ini memuat skenario pengujian alur bisnis fungsional (*Functional End-to-End Business Flows*) yang mencakup 4 role utama: **Customer**, **Mekanik**, **Kurir**, dan **Admin**.

---

## 1. Alur Bisnis 1: Servis Reguler Datang Langsung (Walk-In Booking)

### Deskripsi Alur:
Customer booking servis biasa -> Admin konfirmasi & auto-assign mekanik -> Mekanik diagnosa & tambah sparepart -> Servis selesai -> Customer bayar transfer -> Admin verifikasi pembayaran.

| Langkah | Aktor | Aksi / Endpoint | Data Uji | Hasil yang Diharapkan | Status |
|:---:|---|---|---|---|:---:|
| 1 | Customer | POST `/api/v1/vehicles` | No Polisi: `B 1234 K6`, Merk: `Honda`, Model: `Vario 160` | Kendaraan berhasil didaftarkan (201) | **PASS** |
| 2 | Customer | POST `/api/v1/bookings` | Vehicle ID, tanggal besok, tipe: `servis_ringan` | Booking berstatus `pending` | **PASS** |
| 3 | Admin | POST `/api/v1/bookings/{id}/confirm` | Booking ID | Booking `confirmed`, Service Order dibuat otomatis, mekanik terspesialisasi ditugaskan | **PASS** |
| 4 | Mekanik | POST `/api/v1/service-orders/{id}/start` | Order ID | Status Service Order berubah menjadi `in_progress` | **PASS** |
| 5 | Mekanik | POST `/api/v1/service-orders/{id}/diagnosis` | Catatan: "Perlu penggantian oli dan pembersihan CVT" | Diagnosis tersimpan di database | **PASS** |
| 6 | Mekanik | POST `/api/v1/service-orders/{id}/items` | Item ID sparepart (Oli MPX2), Qty: 1 | Stok sparepart terpotong dari 50 ke 49, snapshot harga terkunci | **PASS** |
| 7 | Mekanik | POST `/api/v1/service-orders/{id}/complete` | Order ID | Status servis menjadi `completed`, invoice terbit | **PASS** |
| 8 | Customer | GET `/api/v1/invoices/{order_id}` | Order ID | Tagihan menampilkan subtotal jasa + sparepart secara akurat | **PASS** |
| 9 | Customer | POST `/api/v1/service-orders/{id}/payments` | Metode: `transfer`, bukti transfer file image | Pembayaran berstatus `pending`, audit log tercatat | **PASS** |
| 10 | Admin | POST `/api/v1/payments/{id}/verify` | Payment ID | Pembayaran `verified`, booking `completed`, notifikasi terkirim | **PASS** |

---

## 2. Alur Bisnis 2: Layanan Servis dengan Antar-Jemput (Pickup & Delivery)

### Deskripsi Alur:
Customer booking dengan fasilitas pickup -> Kurir jemput motor -> Motor tiba di bengkel -> Mekanik ditugaskan otomatis -> Servis dikerjakan -> Pembayaran diverifikasi -> Kurir antar motor kembali ke customer.

| Langkah | Aktor | Aksi / Endpoint | Data Uji | Hasil yang Diharapkan | Status |
|:---:|---|---|---|---|:---:|
| 1 | Customer | POST `/api/v1/bookings` | Opsi `pickup=true`, alamat penjemputan, jarak: 5 km | Delivery task pickup terbuat otomatis dengan estimasi ongkir | **PASS** |
| 2 | Admin | POST `/api/v1/delivery-tasks/{id}/assign` | Courier ID | Task terhubung ke kurir, status `assigned` | **PASS** |
| 3 | Kurir | POST `/api/v1/delivery-tasks/{id}/start` | Task ID | Status task berubah menjadi `in_transit` | **PASS** |
| 4 | Kurir | POST `/api/v1/delivery-tasks/{id}/complete` | Task ID | Task `completed`, Service Order langsung dibuat dan mekanik di-assign otomatis secara idempoten | **PASS** |
| 5 | Mekanik | POST `/api/v1/service-orders/{id}/start` & `/complete` | Order ID & Diagnosis | Servis dikerjakan dan diselesaikan | **PASS** |
| 6 | Customer | POST `/api/v1/service-orders/{id}/payments` | Metode: `transfer` | Total bayar mencakup biaya servis + ongkir pickup & delivery | **PASS** |
| 7 | Admin | POST `/api/v1/payments/{id}/verify` | Payment ID | Pembayaran sah, delivery task pengantaran kembali diaktifkan | **PASS** |
| 8 | Kurir | POST `/api/v1/delivery-tasks/{id_deliv}/complete` | Task ID Pengantaran | Motor sampai ke customer, transaksi selesai penuh | **PASS** |

---

## 3. Alur Bisnis 3: Penolakan Pembayaran & Pengajuan Bukti Ulang

### Deskripsi Alur:
Customer upload bukti transfer palsu/buram -> Admin tolak pembayaran -> Customer upload bukti baru -> Admin verifikasi.

| Langkah | Aktor | Aksi / Endpoint | Data Uji | Hasil yang Diharapkan | Status |
|:---:|---|---|---|---|:---:|
| 1 | Customer | POST `/api/v1/service-orders/{id}/payments` | Bukti bayar buram | Status payment `pending` | **PASS** |
| 2 | Admin | POST `/api/v1/payments/{id}/reject` | Alasan: "Bukti transfer tidak terbaca / tidak valid" | Status payment `rejected`, notifikasi alasan dikirim ke customer | **PASS** |
| 3 | Customer | POST `/api/v1/service-orders/{id}/payments` | Bukti transfer baru yang jelas | Pembayaran baru terbuat, menimpa referensi pembayaran yang ditolak | **PASS** |
| 4 | Admin | POST `/api/v1/payments/{id}/verify` | Payment ID baru | Pembayaran sukses diverifikasi (`verified`) | **PASS** |

---

## 4. Alur Bisnis 4: Manajemen Master Data & Pengaturan oleh Admin

| Langkah | Aktor | Aksi / Endpoint | Data Uji | Hasil yang Diharapkan | Status |
|:---:|---|---|---|---|:---:|
| 1 | Admin | POST `/api/v1/service-items` | Kategori: `sparepart`, Nama: `Kampas Rem`, Stok: 20 | Item tersimpan di master data | **PASS** |
| 2 | Admin | PATCH `/api/v1/service-items/{id}/stock` | Stok baru: 25 | Audit log tercatat, stok terupdate | **PASS** |
| 3 | Admin | PUT `/api/v1/delivery-settings` | `price_per_km`: 4000 | Tarif baru aktif untuk transaksi baru tanpa mengubah snapshot transaksi lama | **PASS** |
| 4 | Admin | PUT `/api/v1/payment-settings` | Ganti nama rekening & QRIS | Instruksi bayar terupdate secara real-time bagi customer | **PASS** |

