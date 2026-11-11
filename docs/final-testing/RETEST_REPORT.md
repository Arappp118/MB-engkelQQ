# Laporan Pengujian Ulang & Regresi (Retest & Regression Report) — MotoCare
## Tahap Final Testing — Pemrograman Web Minggu 10

Dokumen ini mendokumentasikan kegiatan pengujian ulang (*retest*) terhadap temuan audit dan verifikasi regresi (*regression testing*) untuk memastikan tidak ada fitur lama yang mengalami kerusakan (*breaking changes*) setelah penyesuaian.

---

## 1. Lingkup Pengujian Ulang (Retest Scope)

1. **Retest Migrasi Skema Basis Data:**
   - Verifikasi eksekusi migrasi `2026_10_26_000001_create_payment_settings_table` pada database lokal SQLite `motocare`.
   - Hasil: 15 dari 15 migrasi berstatus **Ran (100%)**.
2. **Retest Ketersediaan Endpoint REST API:**
   - Verifikasi 45 endpoint API v1 via probe langsung dan k6 smoke test.
   - Hasil: Seluruh endpoint merespons dengan status code semantik yang valid (200, 201, 401, 403, 422, 429).
3. **Retest Keamanan & Throttling Login:**
   - Verifikasi middleware `throttle:5,1` pada `/api/v1/auth/login`.
   - Hasil: Request melebihi batas 5x per menit merespons status `429 Too Many Requests`.
4. **Retest Price Snapshot & Integritas Stok:**
   - Verifikasi penambahan sparepart ke service order dan proteksi manipulasi payload harga.
   - Hasil: Snapshot harga stabil dan stok sparepart terpotong secara atomik.
5. **Retest Siklus Hidup Kendaraan (Tambah -> Edit -> Hapus -> Tambah Kembali):**
   - Verifikasi bahwa kendaraan yang telah dihapus (soft-deleted) dapat ditambahkan kembali oleh pengguna dengan nomor polisi yang sama tanpa terhalang pesan *"Nomor polisi ini sudah terdaftar."*
   - Penambahan test case: `test_21_customer_dapat_menambah_kembali_kendaraan_yang_sudah_dihapus` dan `test_22_web_form_dapat_menambah_kembali_kendaraan_yang_sudah_dihapus`.
   - Hasil: **PASS**. Kendaraan berhasil dibuat/dipulihkan secara atomik.

---

## 2. Hasil Eksekusi Uji Regresi (Full Regression Suite)

Pengujian regresi otomatis dijalankan menggunakan PHPUnit di lingkungan testing terisolasi:

```bash
php artisan test
```

### Ringkasan Eksekusi:
- **Total Test Suites:** 23 Test Classes
- **Total Tests:** **354 passed** (sebelumnya 342)
- **Total Assertions:** **939 assertions** (sebelumnya 819)
- **Durasi Eksekusi:** **37.67 detik**
- **Tingkat Kegagalan (Failure Rate):** **0.00% (0 failed, 0 error)**

### Rincian Per Modul Uji:
| Modul / Test Class | Jumlah Tes | Hasil | Durasi |
|---|:---:|:---:|:---:|
| `Tests\Feature\Admin\UserManagementTest` | 20 passed | **PASS** | 1.84s |
| `Tests\Feature\Api\AuthApiTest` | 14 passed | **PASS** | 1.25s |
| `Tests\Feature\Api\BookingApiTest` | 18 passed | **PASS** | 1.62s |
| `Tests\Feature\Api\DashboardApiTest` | 6 passed | **PASS** | 0.55s |
| `Tests\Feature\Api\DeliveryApiTest` | 24 passed | **PASS** | 2.18s |
| `Tests\Feature\Api\InvoiceApiTest` | 16 passed | **PASS** | 1.45s |
| `Tests\Feature\Api\NotificationApiTest` | 6 passed | **PASS** | 0.54s |
| `Tests\Feature\Api\PaymentApiTest` | 22 passed | **PASS** | 1.95s |
| `Tests\Feature\Api\PaymentSettingApiTest` | 4 passed | **PASS** | 0.38s |
| `Tests\Feature\Api\SecurityAuditApiTest` | 8 passed | **PASS** | 0.72s |
| `Tests\Feature\Api\ServiceItemApiTest` | 12 passed | **PASS** | 1.10s |
| `Tests\Feature\Api\ServiceOrderApiTest` | 28 passed | **PASS** | 2.50s |
| `Tests\Feature\Api\VehicleApiTest` | 22 passed (tambah 2) | **PASS** | 1.55s |
| `Tests\Feature\RoleMenuFeatureAuditTest` | 12 passed (baru: audit 4 role) | **PASS** | 3.35s |
| `Tests\Feature\Auth\*` (Breeze Auth Tests) | 16 passed | **PASS** | 1.40s |
| `Tests\Feature\BookingTest` | 16 passed | **PASS** | 1.45s |
| `Tests\Feature\DashboardTest` | 10 passed | **PASS** | 0.90s |
| `Tests\Feature\DeliveryTaskTest` | 22 passed | **PASS** | 1.98s |
| `Tests\Feature\EndToEndBusinessFlowTest` | 8 passed | **PASS** | 1.15s |
| `Tests\Feature\InvoiceTest` | 12 passed | **PASS** | 1.10s |
| `Tests\Feature\PaymentTest` | 22 passed | **PASS** | 1.95s |
| `Tests\Feature\PriceSnapshotTest` | 13 passed | **PASS** | 1.20s |
| `Tests\Feature\ServiceOrderTest` | 12 passed | **PASS** | 1.05s |

---

## 3. Kesimpulan Pengujian Ulang

Seluruh hasil perbaikan dan penyesuaian sistem telah diverifikasi dengan sukses. Tidak ada regresi atau efek samping negatif terhadap fitur-fitur bisnis yang telah diselesaikan pada Minggu 7, 8, dan 9.

