# Checklist Verifikasi QC Akhir — MotoCare (MB-engkelQQ)

Dokumen checklist ini memuat seluruh butir kriteria kontrol kualitas (Quality Control) backend Laravel MotoCare yang dievaluasi pada sesi audit 27 Oktober 2026.

---

## 1. Matriks Checklist Verifikasi

| No | Kriteria / Fitur | Metode Pengujian | Hasil Pengujian | Status |
|---|---|---|---|:---:|
| **1** | **Health Check API** | `GET /api/v1/health` via curl | HTTP 200, response JSON standar `{success:true}` | **PASS** |
| **2** | **Sanctum Login Valid** | `POST /api/v1/auth/login` dengan kredensial sah | HTTP 200, token plain text dan data user dihasilkan | **PASS** |
| **3** | **Sanctum Login Invalid** | `POST /api/v1/auth/login` kredensial salah | HTTP 422, pesan kesalahan terstruktur tanpa exception leak | **PASS** |
| **4** | **Rate Limiter Login** | 6x login beruntun dalam 1 menit | HTTP 429 Too Many Requests aktif pada percobaan ke-6 | **PASS** |
| **5** | **Token Revocation (Logout)**| `POST /api/v1/auth/logout` lalu cek `/me` | HTTP 200 pada logout, HTTP 401 saat token lama digunakan | **PASS** |
| **6** | **Proteksi Endpoint Tanpa Auth**| `GET /api/v1/vehicles` tanpa header Authorization | HTTP 401 Unauthorized dengan pesan login wajib | **PASS** |
| **7** | **Isolasi Kendaraan Customer** | `GET /api/v1/vehicles` customer A vs customer B | Hanya mengembalikan kendaraan milik user ID penelpon | **PASS** |
| **8** | **IDOR Akses Kendaraan Lain** | `GET /api/v1/vehicles/{id}` milik customer lain | HTTP 403 Forbidden | **PASS** |
| **9** | **Validasi Nopol & Data Mesin**| Submit form kendaraan tanpa nomor polisi | HTTP 422 Unprocessable Entity | **PASS** |
| **10**| **Pembuatan Booking** | `POST /api/v1/bookings` data valid | HTTP 201 Created, nomor booking unik digenerate | **PASS** |
| **11**| **Konfirmasi Booking Admin** | `POST /api/v1/bookings/{id}/confirm` oleh admin | HTTP 200, status booking menjadi confirmed/assigned | **PASS** |
| **12**| **Penolakan Konfirmasi Ganda**| `POST /api/v1/bookings/{id}/confirm` berulang kali| HTTP 422 Unprocessable Entity ditolak | **PASS** |
| **13**| **Auto Assign Mekanik** | Evaluasi `MechanicAssignmentService` | Mencocokkan tipe mesin dan beban tugas terendah | **PASS** |
| **14**| **Mekanik Mulai Servis** | `POST /api/v1/service-orders/{id}/start` | Status order beralih ke `in_progress` | **PASS** |
| **15**| **Wajib Diagnosis Mekanik** | Complete order tanpa diagnosis | Ditolak oleh server (aturan BR-012) | **PASS** |
| **16**| **Wajib Tindakan Servis/Item**| Complete order tanpa item servis | Ditolak oleh server (aturan BR-012) | **PASS** |
| **17**| **Snapshot Harga Master Item** | Perubahan harga master pada pesanan lama | Harga order item historis tidak berubah | **PASS** |
| **18**| **Pemotongan Stok Sparepart** | Tambah item sparepart ke service order | Stok berkurang atomik sesuai quantity | **PASS** |
| **19**| **Pencegahan Stok Negatif** | Order sparepart melebihi stok fisik | Ditolak oleh server, stok tidak berubah | **PASS** |
| **20**| **Perhitungan Grand Total** | Subtotal jasa + sparepart + ongkir | Dihitung server, manipulasi request diabaikan | **PASS** |
| **21**| **Penyelesaian Order Servis** | `POST /api/v1/service-orders/{id}/complete` | Status order `completed`, booking `service_completed` | **PASS** |
| **22**| **Pengajuan Pembayaran** | `POST /api/v1/service-orders/{id}/payments` | Status `waiting_verification`, `paid_at` NULL | **PASS** |
| **23**| **Pencegahan Race Condition Bayar**| Request bayar ganda bersamaan | Database row locking (`lockForUpdate`) menolak duplikat | **PASS** |
| **24**| **Penolakan Bayar Non-Owner** | Customer B bayar order customer A | HTTP 403 Forbidden | **PASS** |
| **25**| **Verifikasi Pembayaran Admin**| `POST /api/v1/payments/{id}/verify` oleh admin | Status `verified`, `paid_at` terisi timestamp aktual | **PASS** |
| **26**| **Penolakan Verifikasi Staf** | Mekanik/Kurir verifikasi pembayaran | HTTP 403 Forbidden | **PASS** |
| **27**| **Alur Reject Pembayaran** | `POST /api/v1/payments/{id}/reject` alasan buram | Status `rejected`, alasan tercatat pada notes | **PASS** |
| **28**| **Pengajuan Ulang Bukti Bayar**| Customer re-submit setelah reject | Pembayaran baru terbit berstatus waiting_verification | **PASS** |
| **29**| **Transisi Booking Sah Delivery**| Order dengan tugas kurir diverifikasi bayar | Booking beralih ke `ready_for_delivery` | **PASS** |
| **30**| **Penugasan Kurir Pickup** | `POST /api/v1/delivery-tasks/{id}/assign` | Task beralih ke `assigned`, kurir menerima notifikasi | **PASS** |
| **31**| **Kurir Memproses Delivery** | `POST /api/v1/delivery-tasks/{id}/complete` | Status task `completed`, fee terkunci dari snapshot | **PASS** |
| **32**| **Konfigurasi Rekening & QRIS**| `PUT /api/v1/payment-settings` oleh admin | Berhasil diperbarui dan tercatat pada audit log | **PASS** |
| **33**| **Non-Admin Ubah Rekening/QRIS**| `PUT /api/v1/payment-settings` oleh customer | HTTP 403 Forbidden | **PASS** |
| **34**| **Dashboard Multi-Role** | `GET /api/v1/dashboard` tiap role | Metrik data menyesuaikan role pengguna otomatis | **PASS** |
| **35**| **Notifikasi Milik Sendiri** | `GET /api/v1/notifications` | Hanya memuat notifikasi user login | **PASS** |
| **36**| **Pencatatan Audit Trail** | Verifikasi tabel `audit_logs` | Tindakan finansial & inventaris tercatat dengan aktor | **PASS** |
| **37**| **Integrasi Gateway Pihak Ketiga**| Webhook provider otomatis (Midtrans/Xendit)| Memerlukan kredensial sandbox/prod resmi | **BLOCKED** |
| **38**| **Live Browser Testing Desktop/Mobile**| Otomatisasi browser pada DB lokal | Ditiadakan demi keamanan data MariaDB pengguna | **BLOCKED** |

---

## 2. Kesimpulan Checklist
- **Item Teruji:** 36 Item
- **Item Lulus (PASS):** 36 Item (100% dari item yang dapat diuji)
- **Item Gagal (FAIL):** 0 Item
- **Item Diblokir (BLOCKED):** 2 Item (Keterbatasan kredensial merchant eksternal dan perlindungan database operasional).
