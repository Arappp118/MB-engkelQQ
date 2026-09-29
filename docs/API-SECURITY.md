# MotoCare API Security

## Authentication
Laravel Sanctum (Personal Access Token). Token dibuat saat login, di-revoke satu-per-satu saat logout (`currentAccessToken()->delete()`) — tidak pernah menghapus semua token user sekaligus.

## Authorization
Semua endpoint (kecuali `/health` dan `/auth/login`) wajib `auth:sanctum`. Otorisasi per-aksi memakai Laravel Policy yang sama dengan Web (`VehiclePolicy`, `BookingPolicy`, `ServiceOrderPolicy`, `ServiceItemPolicy`, `PaymentPolicy`, `DeliveryTaskPolicy`, `AppNotificationPolicy`) — tidak ada duplikasi/bypass aturan.

## IDOR Protection
Setiap resource yang di-scope kepemilikan (Vehicle, Booking, ServiceOrder, Payment, DeliveryTask, Notification, Invoice) diverifikasi lewat Policy sebelum data dikembalikan. Percobaan akses resource milik user lain menghasilkan 403.

## Ownership
`user_id`/`customer_id`/`courier_id` **tidak pernah** diambil dari body request — selalu dari `$request->user()->id` di level Controller/Service. Field ownership yang dikirim client (mis. `user_id` di body Vehicle) diabaikan sepenuhnya karena tidak ada di whitelist Form Request `rules()`.

## Validation
Semua endpoint yang menerima input memakai Laravel Form Request server-side. API me-reuse Form Request yang sama dengan Web bila validasinya identik (tidak ada logic ganda).

## Rate Limiting
`POST /auth/login` dibatasi 5 percobaan/menit per kombinasi IP+session untuk mencegah brute-force. Melebihi batas mengembalikan HTTP 429.

## Mass Assignment
Semua Form Request memakai whitelist eksplisit di `rules()`. Field harga (`price`, `amount`, `delivery_fee`, `price_snapshot`, `subtotal`, `grand_total`) tidak pernah menjadi bagian dari `rules()` pada endpoint transaksi — nilai selalu dihitung/diambil server-side dari database.

## File Upload Security (Payment Proof)
- MIME whitelist: `jpg`, `jpeg`, `png`, `pdf`
- Ukuran maksimal: 2MB
- Disimpan di disk `local` (`storage/app/private`) — **tidak** publicly accessible
- Path internal tidak pernah diekspos di response API (`PaymentResource` hanya mengembalikan `has_proof: boolean`)

## Price & Snapshot Integrity
- Harga master (`ServiceItem.price`) hanya bisa diubah Admin
- Setiap item transaksi (`ServiceOrderItem`) menyimpan snapshot nama & harga saat ditambahkan — perubahan harga master setelahnya **tidak** mengubah transaksi lama (teruji)
- `DeliveryTask.delivery_fee` di-snapshot saat task dibuat — perubahan tarif admin **tidak** mengubah fee task lama (teruji)
- Invoice selalu memakai data snapshot historis, tidak pernah menghitung ulang dari harga master saat ini

## Error Handling
Custom exception handler (`bootstrap/app.php`) memastikan setiap error API mengembalikan format JSON standar (`success`, `message`, `errors`) dengan HTTP status code yang benar (401/403/404/405/422/429/500). Stack trace dan pesan exception internal **tidak pernah** diekspos, apapun nilai `APP_DEBUG`.

## HTTPS Production Requirement
Di production, API **wajib** diakses lewat HTTPS. Token Sanctum dikirim sebagai Bearer header — tanpa HTTPS, token rentan disadap (man-in-the-middle). Pastikan `APP_URL` menggunakan `https://` dan web server (Nginx/Apache) memaksa redirect HTTP → HTTPS sebelum deployment.
