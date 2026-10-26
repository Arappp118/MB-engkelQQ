# Dokumentasi QA & Audit Pembayaran — MotoCare (MB-engkelQQ)

Dokumen ini menyajikan hasil audit mendalam, arsitektur perbaikan, SOP verifikasi, instruksi deployment, dan hasil QA/QC pada modul pembayaran MotoCare.

---

## 1. Arsitektur & Siklus Status Pembayaran

### A. Lifecycle Status Pembayaran
Status pembayaran pada sistem dimodelkan dengan siklus berikut:

```
[Customer Submit]
       │
       ▼
waiting_verification  ──(Admin Tolak: reject)──►  rejected
       │                                             │
 (Admin Verifikasi)                                  ▼
       │                                  [Customer Re-submit Form]
       ▼                                             │
    verified  (paid_at diisi)                        ▼
       │                                    waiting_verification
       ▼
 [Lunas / Order Siap Diambil / Diantar]
```

1. **`waiting_verification` (Menunggu Verifikasi)**:
   - Status awal saat Customer mengajukan pembayaran via Transfer Bank, QRIS, maupun Cash.
   - **`paid_at` bernilai `NULL`**. Customer mengirim bukti bayar (foto/PDF), namun sistem **tidak menganggap uang telah diterima**.
   - Menghalangi pengajuan pembayaran ganda (*pessimistic lock* pada Service Order dan pengecekan pembayaran aktif).
2. **`rejected` (Ditolak)**:
   - Terjadi jika Admin memeriksa rekening/mutasi dan menemukan bukti palsu, nominal tidak cocok, atau transaksi belum masuk.
   - Wajib menyertakan alasan penolakan (`notes`).
   - Customer menerima notifikasi dan **dapat mengajukan kembali pembayaran baru** pada Service Order yang sama.
3. **`verified` (Terverifikasi / Lunas)**:
   - Ditetapkan secara eksplisit oleh Admin berwenang setelah pencocokan mutasi manual berhasil.
   - Kolom `verified_by`, `verified_at`, dan **`paid_at`** diisi dengan waktu timestamp aktual (`now()`).
   - Memicu transisi status Service Order dan Booking secara sah (`canTransitionTo`). Jika order memiliki tugas pengantaran (`delivery_task`), booking beralih ke `ready_for_delivery`. Jika tidak, beralih ke `completed`.
4. **`paid`**:
   - Status terminologi setara lunas yang dikenali sistem pada invoice dan payment.

---

## 2. Implementasi Alur Pembayaran

### A. Transfer Bank (Manual)
1. **Penyimpanan Rekening**: Dikelola secara dinamis oleh Admin melalui tabel `payment_settings` (menu Web `/admin/payment-settings` dan API `/api/v1/payment-settings`). Tidak ada nomor rekening pribadi yang di-*hardcode* di source code.
2. **Alur Customer**:
   - Customer membuka detail Service Order atau Invoice.
   - Sistem menampilkan rekening aktif (Nama Bank, Nomor Rekening, Atas Nama).
   - Customer melakukan transfer mandiri sebesar nominal eksak `grand_total` server.
   - Customer mengunggah bukti transfer (file jpg/png/pdf max 2MB).
   - Status menjadi `waiting_verification`.
3. **Alur Admin**:
   - Admin membuka `/payments/{payment}`.
   - Bukti bayar diamankan via route berotorisasi `route('payments.proof', $payment)` (bukan file publik telanjang).
   - Admin mencocokkan mutasi rekening bengkel.
   - Admin klik **Verifikasi** jika valid, atau **Tolak** dengan alasan.

### B. QRIS Statis Merchant Bengkel
1. **Penyimpanan QRIS**: Gambar QRIS resmi bengkel dan nama merchant dikelola oleh Admin melalui menu pengaturan pembayaran.
2. **Alur Customer**:
   - Customer memilih metode QRIS.
   - Menampilkan QR Code resmi bengkel, nama merchant, dan instruksi jelas: *"Scan QRIS di atas menggunakan aplikasi m-Banking atau e-Wallet pilihan Anda. Pastikan nominal transfer sesuai tepat dengan tagihan."*
   - Customer mengunggah bukti screenshot pembayaran QRIS.
3. **Alur Admin**:
   - Admin memverifikasi bukti screenshot terhadap notifikasi mutasi QRIS merchant bengkel sebelum menekan tombol Verifikasi.

### C. Pembayaran Tunai (Cash)
1. Customer memilih opsi Tunai di kasir/bengkel.
2. Pengajuan berstatus `waiting_verification` dan kasir/admin memverifikasi penerimaan uang fisik di bengkel sebelum status berubah menjadi `verified` / `paid_at`.

---

## 3. Otorisasi & Pencegahan Celah Keamanan (Hardening)

1. **Integritas Nominal (Tamper-Proof)**:
   - Input `amount` dari form atau payload request klien **diabaikan**.
   - Nominal pembayaran diambil langsung secara atomik dari `$lockedOrder->grand_total` di sisi server.
2. **Pencegahan Race Condition**:
   - Eksekusi pembuatan pembayaran dibungkus dalam `DB::transaction`.
   - Menggunakan `lockForUpdate()` pada record `ServiceOrder`.
   - Mengunci dan memeriksa keberadaan pembayaran aktif (`waiting_verification`, `verified`, `paid`). Jika ada, request ditolak dengan exception.
3. **Pemberlakuan Otorisasi Eksplisit**:
   - Controller Web & API dilindungi secara eksplisit oleh `$this->authorize('reject', $payment)` dan `$this->authorize('verify', $payment)`.
   - Policy memastikan hanya role `admin` yang dapat memverifikasi atau menolak pembayaran.
   - Role `customer` hanya dapat melihat dan mengunggah pembayaran untuk Service Order miliknya sendiri.
   - Mekanik dan Kurir dilarang mengakses pembayaran customer lain.
4. **Keamanan Bukti Pembayaran**:
   - Bukti pembayaran disimpan pada disk penyimpanan internal dengan validasi tipe berkas (`mimes:jpg,jpeg,png,pdf|max:2048`).
   - Unduhan/tampilan berkas dilindungi oleh otorisasi controller.
5. **Audit Trail**:
   - Setiap perubahan pengaturan rekening/QRIS dicatat pada `AuditLog` dengan aksi `payment_settings_update`.
   - Verifikasi dan penolakan pembayaran dicatat pada `AuditLog` lengkap dengan aktor admin dan alasan penolakan.

---

## 4. Pengaturan Admin & Konfigurasi Lingkungan

### A. Konfigurasi Rekening & QRIS
Admin dapat mengelola konfigurasi melalui:
- **Web**: `/admin/payment-settings`
- **API**: `PUT /api/v1/payment-settings`

Parameter yang dikelola:
| Kunci | Tipe | Deskripsi |
|---|---|---|
| `bank_name` | string | Nama Bank (contoh: BCA, Mandiri, BRI) |
| `bank_account_number` | string | Nomor Rekening resmi bengkel |
| `bank_account_holder` | string | Nama Pemilik Rekening sesuai buku tabungan |
| `bank_is_active` | boolean | Status aktif transfer bank (true/false) |
| `qris_merchant_name` | string | Nama Merchant QRIS |
| `qris_image_path` | string/file | Path file gambar QRIS resmi |
| `qris_is_active` | boolean | Status aktif pembayaran QRIS (true/false) |

### B. Environment Variables (`.env`)
Tidak ada kredensial bank atau secret rahasia yang di-*hardcode* ke repository. Jika integrasi payment gateway di masa mendatang diaktifkan, gunakan template variabel lingkungan berikut:

```env
# Konfigurasi Storage Pembayaran
PAYMENT_PROOF_DISK=public

# Konfigurasi Payment Gateway di Masa Depan (Placeholder)
# MIDTRANS_SERVER_KEY=your-midtrans-server-key-here
# MIDTRANS_CLIENT_KEY=your-midtrans-client-key-here
# MIDTRANS_IS_PRODUCTION=false
```

---

## 5. Keterbatasan Eksternal (External Limitations)

> [!WARNING]
> **Status Integrasi Gateway Otomatis: BLOCKED — Memerlukan Konfigurasi / Provider yang Sah**
>
> Proyek saat ini **tidak memiliki** package SDK payment gateway (seperti Midtrans/Xendit/DOKU) maupun kredensial merchant resmi pada `composer.json` dan `.env`.
> Sesuai arahan audit, sistem mengimplementasikan alur **Bank Transfer & QRIS Statis Terverifikasi Manual oleh Admin**.
> Webhook otomatis dengan verifikasi signature dari gateway pihak ketiga tidak diimplementasikan secara spekulatif tanpa dokumentasi dan akun merchant yang sah.

---

## 6. Hasil Pengujian QA/QC

Seluruh test suite dijalankan menggunakan PHPUnit di lingkungan lokal:

```bash
php artisan test
```

### Ringkasan Eksekusi Test:
- **Total Tests**: **336 Passed** (0 Failed, 0 Errors, 0 Skipped).
- **Total Assertions**: **740 Assertions**.
- **Durasi**: ~12.2s.

### Rincian Modul yang Diuji:
1. **Payment Web Feature Test (`tests/Feature/PaymentTest.php`)**:
   - Pembuatan pembayaran dengan `paid_at` awal `NULL`.
   - Transisi `waiting_verification` -> `verified` mengisi `paid_at` dan `verified_at`.
   - Pencegahan manipulasi amount dari form request.
   - Penolakan submission duplikat saat ada pembayaran aktif.
   - Pengajuan ulang pembayaran setelah pembayaran lama ditolak (`rejected`).
   - Validasi otorisasi admin untuk verifikasi dan penolakan.
2. **Payment API Feature Test (`tests/Feature/Api/PaymentApiTest.php`)**:
   - Endpoint `/api/v1/service-orders/{order}/payments` (Customer).
   - Endpoint `/api/v1/payments/{payment}/verify` dan `reject` (Admin).
   - Penolakan akses Customer/Kurir/Mekanik pada endpoint verifikasi (HTTP 403).
   - Penolakan race condition / pembayaran duplikat (HTTP 422).
3. **Payment Settings Test (`tests/Feature/PaymentSettingTest.php` & `Api/PaymentSettingApiTest.php`)**:
   - Hak akses eksklusif Admin untuk mengubah rekening dan QRIS.
   - Customer hanya dapat membaca instruksi rekening/QRIS yang berstatus aktif.
4. **Regresi Seluruh Role**:
   - Customer: Kendaraan, Booking, Keluhan, Invoice, Notifikasi (Pass).
   - Mekanik: Diagnosis, Service Order, Penggunaan Sparepart, Stok (Pass).
   - Kurir: Penugasan Delivery Task, Status Pickup/Delivery (Pass).
   - Admin: User, Role, Sparepart Stock, Konfigurasi Tarif, Audit Log (Pass).
