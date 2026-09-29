# MotoCare API Testing

## Menjalankan Test

Seluruh test API:
```bash
php artisan test tests/Feature/Api
```

Test spesifik per modul:
```bash
php artisan test --filter=AuthApiTest
php artisan test --filter=VehicleApiTest
php artisan test --filter=BookingApiTest
php artisan test --filter=ServiceOrderApiTest
php artisan test --filter=ServiceItemApiTest
php artisan test --filter=PaymentApiTest
php artisan test --filter=DeliveryApiTest
php artisan test --filter=InvoiceApiTest
php artisan test --filter=NotificationApiTest
php artisan test --filter=DashboardApiTest
```

Full regression (seluruh test project, Web + API):
```bash
php artisan test
```

## Database Testing
Test memakai SQLite in-memory (`phpunit.xml`: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`), **tidak pernah** menyentuh database MySQL development/production.

## Cakupan Test per Modul
| Modul | Jumlah Test | Fokus |
|---|---|---|
| AuthApiTest | 16 | Login, token, logout, rate limit, role tidak bisa dimanipulasi |
| VehicleApiTest | 20 | CRUD, ownership, IDOR, business rule delete |
| BookingApiTest | 15 | CRUD, ownership vehicle, status guard, mechanic assignment |
| ServiceOrderApiTest | 12 | Ownership mekanik, pemisahan diagnosis, status guard |
| ServiceItemApiTest | 9 | Admin-only pricing, item nonaktif |
| PaymentApiTest | 11 | Amount server-side, verify/reject, upload aman |
| DeliveryApiTest | 11 | Fee snapshot, assignment, tarif admin-only |
| InvoiceApiTest | 8 | Read-only, snapshot historis, IDOR |
| NotificationApiTest | 5 | Isolasi user, mark as read |
| DashboardApiTest | 7 | Isolasi data per role |

## Catatan Metodologi Test — Sanctum Guard Caching
Dalam **satu test method**, memanggil 2 HTTP request berurutan dengan token berbeda dapat memicu Sanctum meng-cache user hasil resolusi pertama (artefak testing, bukan bug produksi — di request HTTP asli setiap request adalah proses baru). Solusinya:
1. Pisah jadi 2 test method terpisah (dipakai di `BookingApiTest`, `ServiceItemApiTest`), **atau**
2. Verifikasi lewat query database langsung, bukan HTTP call kedua (dipakai di `AuthApiTest` untuk validasi token setelah logout).

## Test Keamanan (Security-Focused)
- **IDOR**: setiap modul dengan kepemilikan data punya test eksplisit "customer A akses data customer B → 403"
- **Mass assignment**: test eksplisit mengirim field terlarang (`user_id`, `price`, `amount`, `delivery_fee`, `status`) dan memverifikasi nilai asli tidak berubah
- **File upload**: test file tidak valid (ekstensi salah, ukuran terlalu besar) ditolak 422
- **Rate limiting**: test 6 percobaan login gagal berturut-turut → 429 pada percobaan ke-6

## Regression
Setiap fase (11D–11K) menjalankan test modulnya sendiri dulu, baru full regression (`php artisan test`) untuk memastikan tidak ada modul lain yang rusak, sebelum lanjut ke fase berikutnya.
