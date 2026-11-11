# Laporan Pengelolaan Bug (Bug Management Report) — MotoCare
## Tahap Final Testing — Pemrograman Web Minggu 10

---

## 1. Klasifikasi Severity & Priority

- **Severity:**
  - `Critical`: Sistem lumpuh, data korup, celah keamanan fatal (*price tampering / unauthorized bypass*).
  - `High`: Fitur inti tidak berfungsi, transaksi terputus tanpa fallback.
  - `Medium`: Fitur sekunder terganggu, inkonsistensi skema, atau limitasi integrasi eksternal.
  - `Low`: Masalah tampilan, artefak file sampah di repositori, atau notifikasi non-kritis.
- **Priority:**
  - `P1`: Wajib diselesaikan segera sebelum presentasi/rilis.
  - `P2`: Diselesaikan pada sprint berjalan.
  - `P3`: Peningkatan minor atau mitigasi jangka menengah.

---

## 2. Tabel Daftar Bug & Temuan Audit

| Bug ID | Modul | Deskripsi Bug / Temuan | Severity | Priority | Langkah Reproduksi | Hasil Diharapkan | Hasil Aktual | Status Resolusi |
|---|---|---|:---:|:---:|---|---|---|:---:|
| **BUG-W10-01** | Repository / Workspace | Berkas sampah tak sengaja terbentuk `'e --abbrev-ref HEAD\033[201~'` akibat bracketed paste terminal. | Low | P3 | Jalankan `git status` di terminal. | Git working tree bersih tanpa artefak escape sequence. | Muncul berkas tak terlacak berisi baris commit log. | **RESOLVED** (Dibersihkan) |
| **BUG-W10-02** | Database Migration | Migrasi tabel `payment_settings` berstatus pending pada database lokal SQLite `motocare`. | Medium | P1 | Jalankan `php artisan migrate:status --database=sqlite`. | Seluruh 15 migrasi berstatus Ran. | Migrasi `2026_10_26_000001_create_payment_settings_table` berstatus Pending. | **RESOLVED** (Migrated & Verified) |
| **BUG-W10-03** | Payment Gateway | Ketiadaan webhook otomatisasi payment gateway komersial (Midtrans/Xendit) untuk verifikasi otomatis instan. | Medium | P2 | Periksa konfigurasi SDK payment gateway pada `.env`. | Webhook callback instan dari payment gateway. | Menggunakan alur Transfer Bank & QRIS statis dengan manual admin approval demi keamanan data. | **MITIGATED / AS DESIGNED** (Sesuai Batasan Akademik) |
| **BUG-W10-04** | API Auth / Concurrency | Endpoint `/api/v1/auth/login` memblokir pengujian beban tinggi dengan status 429 Too Many Requests akibat `throttle:5,1`. | Low (Security Feature) | P2 | Jalankan skrip k6 memanggil endpoint login dengan 10 VU bersamaan. | Mampu melayani ratusan login per detik jika throttling dilepas. | Request ke-6 dan seterusnya terblokir 429 (brute force protection). | **RESOLVED** (Token diinjeksi via ENV untuk pengujian beban bisnis) |
| **BUG-W10-05** | Delivery & Pricing | Potensi perubahan tarif ongkir master mengubah nominal transaksi yang sedang berjalan. | High | P1 | 1. Buat booking dengan ongkir 15.000.<br>2. Admin ubah tarif ongkir master.<br>3. Periksa invoice lama. | Nominal ongkir transaksi lama tetap terkunci (snapshot). | Terverifikasi terkunci via kolom snapshot `delivery_fee` pada order. | **VERIFIED PASS** (Sudah terlindungi sejak W9) |
| **BUG-W10-06** | Vehicle Management | Kendaraan yang telah dihapus (soft-deleted) tidak dapat ditambahkan kembali dengan plat nomor yang sama karena terganjal validasi unique tanpa pengecekan soft-delete. | High | P1 | 1. Tambah kendaraan (misal: `BP 1234 XX`).<br>2. Hapus kendaraan.<br>3. Coba tambah kembali kendaraan dengan plat yang sama. | Kendaraan berhasil ditambahkan kembali / dipulihkan tanpa error. | Validasi form memunculkan pesan error: *"Nomor polisi ini sudah terdaftar."* | **RESOLVED** (Disesuaikan rule `whereNull('deleted_at')` dan penanganan auto-restore pada `VehicleService::create`) |

---

## 3. Catatan Resolusi & Verifikasi Keamanan

Seluruh bug dan temuan audit telah terselesaikan dan diverifikasi. Fungsionalitas sistem telah divalidasi melalui **342 Feature Tests (819 assertions)** dan 6 skenario pengujian beban k6 dengan tingkat kelulusan **100%**.

