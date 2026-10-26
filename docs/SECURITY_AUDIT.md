# Laporan Audit Keamanan Aplikasi (Application Security Audit) — MotoCare

Dokumen ini menyajikan hasil audit keamanan defensif pada backend Laravel MotoCare (MB-engkelQQ) berdasarkan standar OWASP Top 10 API Security.

---

## 1. Ringkasan Temuan Berdasarkan Severity

| Severity | Jumlah Temuan | Status |
|---|:---:|:---:|
| **Critical** | 0 | **RESOLVED / NOT FOUND** |
| **High** | 0 | **RESOLVED / NOT FOUND** |
| **Medium** | 1 | **MITIGATED** (Rate limit throttle aktif 5/menit pada login) |
| **Low** | 1 | **DOCUMENTED** (Penyimpanan bukti bayar lokal terkunci pada controller berotorisasi) |
| **Informational** | 2 | **RECOMMENDED** (Penambahan CORS domain spesifik dan security headers production) |

---

## 2. Analisis Rinci per Vektor Keamanan

### A. Broken Access Control & IDOR (`STATUS: SECURE`)
- **Implementasi:** Diatur melalui Laravel Policy classes:
  - [`BookingPolicy`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/app/Policies/BookingPolicy.php)
  - [`VehiclePolicy`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/app/Policies/VehiclePolicy.php)
  - [`ServiceOrderPolicy`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/app/Policies/ServiceOrderPolicy.php)
  - [`PaymentPolicy`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/app/Policies/PaymentPolicy.php)
  - [`DeliveryTaskPolicy`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/app/Policies/DeliveryTaskPolicy.php)
- **Pengujian Live:** Percobaan akses resource customer lain menghasilkan response `403 Forbidden`. Akses endpoint admin oleh customer/mekanik/kurir menghasilkan `403 Forbidden`.

### B. Otentikasi & Sanctum Token Lifecycle (`STATUS: SECURE`)
- Token di-issue melalui `createToken()` saat kredensial valid.
- Token dicabut (*deleted*) dari tabel `personal_access_tokens` saat logout.
- Token kadaluwarsa atau yang telah di-revoke secara otomatis ditolak dengan `401 Unauthorized`.
- **Brute Force Protection:** Endpoint `/api/v1/auth/login` dilindungi oleh middleware `throttle:5,1`. Terverifikasi secara live mengembalikan `HTTP 429 Too Many Requests` setelah 5 percobaan dalam 1 menit.

### C. Mass Assignment & Privilege Escalation (`STATUS: SECURE`)
- Field sensitif seperti `role`, `status`, `paid_at`, `verified_by`, `grand_total` tidak dapat dimanipulasi melalui request form.
- Form request admin untuk update staf (`UpdateMechanicRequest`, `UpdateCourierRequest`) mengabaikan input parameter `role`.

### D. Parameter Tampering (Integritas Finansial) (`STATUS: SECURE`)
- Input `amount` pada request pembuatan pembayaran diabaikan oleh server.
- Total pembayaran selalu dibaca langsung dari database server (`$lockedOrder->grand_total`).
- Harga item transaksi disalin dari master harga saat transaksi dibuat (*snapshot price*) dan tidak dapat dimanipulasi oleh mekanik.

### E. SQL Injection & Database Safety (`STATUS: SECURE`)
- Seluruh query menggunakan Query Builder dan Eloquent ORM berparameter (PDO prepared statements).
- Operasi konkuren dilindungi dengan row-level lock (`lockForUpdate()`) di dalam `DB::transaction`.

### F. File Upload & Bukti Pembayaran (`STATUS: SECURE`)
- Upload bukti transfer dibatasi pada ekstensi aman: `jpg, jpeg, png, pdf`.
- Ukuran maksimum dibatasi `2048 KB` (2 MB).
- File disimpan pada storage internal dan diunduh melalui controller `PaymentController::proof` yang mewajibkan otorisasi admin atau pemilik transaksi. Akses publik langsung ke file bukti bayar diblokir.

### G. Cross-Site Scripting (XSS) & CSRF (`STATUS: SECURE`)
- Blade views menggunakan sintaks escape ganda `{{ ... }}` untuk seluruh variabel dinamis.
- Seluruh form web dilindungi oleh token CSRF. Endpoint API murni menggunakan header Bearer Token Sanctum.

### H. Sensitive Data Exposure (`STATUS: SECURE`)
- Password pengguna di-hash menggunakan algoritma Bcrypt.
- Kolom password dan remember token disembunyikan dari serialisasi Eloquent (`$hidden`).
- Error handling API di-wrap dalam format JSON seragam tanpa membocorkan trace exception internal atau nama file PHP.

---

## 3. Rekomendasi Penguatan Lingkungan Production
1. **Security Headers:** Tambahkan middleware penambah header HTTP security seperti `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, dan `Strict-Transport-Security`.
2. **CORS Whitelist:** Batasi `Access-Control-Allow-Origin` hanya pada domain resmi web aplikasi dan aplikasi mobile terdaftar, bukan tanda bintang (`*`).
