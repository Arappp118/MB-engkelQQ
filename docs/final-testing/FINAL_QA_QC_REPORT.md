# Laporan Akhir Jaminan Kualitas (Final QA/QC Report) — MotoCare (MB-engkelQQ)
## Tahap Final Testing — Pemrograman Web Minggu 10

Dokumen ini memuat hasil audit dan pengujian menyeluruh (QA/QC) terhadap 17 modul dan aspek fungsional sistem MotoCare berdasarkan arsitektur nyata, Use Case, Activity Diagram, dan Sequence Diagram sistem.

---

## 1. Ringkasan Eksekutif QA/QC

- **Total Test Cases Dijalankan:** **340 Automated Feature Tests** + **6 Skenario k6 Performance Tests** + **45 REST API Endpoints Verification**.
- **Hasil Pengujian Keseluruhan:** **100% PASS** (340 passed, 0 failed, 0 error).
- **Cakupan Modul:** 17 Aspek Utama Terverifikasi.
- **Status Akhir:** **READY FOR PRODUCTION & PRESENTATION**.

---

## 2. Matriks Pengujian Komprehensif (17 Aspek QA/QC)

| Test Case ID | Modul | Skenario Pengujian | Prekondisi | Langkah-Langkah | Hasil yang Diharapkan | Hasil Aktual | Status | Bukti Pengujian | Bug ID |
|---|---|---|---|---|---|---|:---:|---|:---:|
| **TC-AUTH-01** | Authentication | Login pengguna valid (Customer, Admin, Mekanik, Kurir) | Akun aktif terdaftar di database | 1. POST `/api/v1/auth/login`<br>2. Kirim email & password valid | Mengembalikan status 200, bearer token Sanctum, data user | Status 200, token plainText diterima | **PASS** | `AuthApiTest::test_login_berhasil` | — |
| **TC-AUTH-02** | Authentication | Login dengan password salah | Akun terdaftar di database | 1. POST `/api/v1/auth/login`<br>2. Kirim email valid & password salah | Mengembalikan status 422 Unprocessable Content / invalid credentials | Status 422, pesan error validasi diterima | **PASS** | `AuthApiTest::test_login_gagal_password_salah` | — |
| **TC-AUTH-03** | Authorization | Proteksi rate limiting pada login | Akun terdaftar | 1. Kirim >5 request login gagal berturut-turut dalam 1 menit | Sistem merespons status 429 Too Many Requests | Status 429, header Retry-After ada | **PASS** | `AuthApiTest::test_login_terkena_rate_limit` | — |
| **TC-AUTH-04** | Authorization | Akses rute terproteksi tanpa token | Tidak memiliki token otentikasi | 1. GET `/api/v1/auth/me` tanpa header Authorization | Sistem menolak akses dengan status 401 Unauthorized | Status 401 Unauthorized | **PASS** | `AuthApiTest::test_unauthenticated_menghasilkan_401` | — |
| **TC-PROF-01** | Customer Profile | Pembaruan data profil pengguna | User login sebagai customer | 1. PATCH `/profile`<br>2. Kirim update nama dan email | Profil terupdate di DB, email verification terjaga | Data tersimpan, redirect ke profil dengan status OK | **PASS** | `ProfileTest::profile_information_can_be_updated` | — |
| **TC-VEH-01** | Vehicle Data | Customer mendaftarkan kendaraan baru | User terotentikasi sebagai customer | 1. POST `/api/v1/vehicles`<br>2. Isi no polisi, tipe mesin, transmisi | Kendaraan tersimpan dengan `user_id` milik customer (201 Created) | Kendaraan tersimpan di DB, status 201 | **PASS** | `VehicleApiTest::test_customer_dapat_membuat_kendaraan` | — |
| **TC-VEH-02** | Vehicle Data | Isolasi data kendaraan antar customer | User B mengakses kendaraan User A | 1. GET `/api/v1/vehicles/{id_A}` menggunakan token User B | Mengembalikan status 403 Forbidden | Status 403 Forbidden | **PASS** | `VehicleApiTest::test_user_tidak_dapat_melihat_kendaraan_orang_lain` | — |
| **TC-BOOK-01** | Booking & Complaint | Pembuatan booking servis oleh customer | Customer memiliki kendaraan terdaftar | 1. POST `/api/v1/bookings`<br>2. Pilih kendaraan, tanggal, keluhan | Booking berstatus `pending`, audit log tercatat (201 Created) | Status 201, booking tersimpan di DB | **PASS** | `BookingApiTest::test_customer_dapat_membuat_booking` | — |
| **TC-BOOK-02** | Booking & Complaint | Pembatalan booking sebelum diproses | Booking berstatus `pending` | 1. POST `/api/v1/bookings/{id}/cancel` | Status booking berubah menjadi `cancelled` | Status 200, status `cancelled` di DB | **PASS** | `BookingApiTest::test_customer_dapat_membatalkan_booking_pending` | — |
| **TC-AUTO-01** | Auto-Assignment Mekanik | Penugasan otomatis mekanik saat booking dikonfirmasi | Tersedia mekanik aktif dengan spesialisasi sesuai | 1. POST `/api/v1/bookings/{id}/confirm` oleh Admin | Booking berstatus `confirmed`, service order terbuat, mekanik tertugaskan | Mekanik dengan antrean terendah ditugaskan otomatis | **PASS** | `BookingApiTest::test_admin_dapat_mengonfirmasi_booking` | — |
| **TC-AUTO-02** | Auto-Assignment Mekanik | Penugasan otomatis saat pickup completed | Delivery task pickup selesai | 1. POST `/api/v1/delivery-tasks/{id}/complete` | Service order terbuat otomatis dan mekanik ditugaskan | Service order dibuat idempotently | **PASS** | `DeliveryTaskTest::pickup_completion_dengan_mekanik_otomatis_assign` | — |
| **TC-DIAG-01** | Diagnosis & Service | Mekanik mengisi diagnosis kerusakan | Service order berstatus `in_progress` | 1. POST `/api/v1/service-orders/{id}/diagnosis`<br>2. Isi catatan diagnosis | Diagnosis tersimpan di database, status order valid | Diagnosis tersimpan, audit log tercatat | **PASS** | `ServiceOrderApiTest::test_mekanik_dapat_menyimpan_diagnosis` | — |
| **TC-DIAG-02** | Diagnosis & Service | Validasi penyelesaian servis tanpa diagnosis | Service order aktif | 1. POST `/api/v1/service-orders/{id}/complete` tanpa diagnosis | Sistem menolak dengan status 422 Unprocessable Content | Status 422 ditolak | **PASS** | `ServiceOrderApiTest::test_tidak_dapat_menyelesaikan_servis_tanpa_diagnosis` | — |
| **TC-SPAR-01** | Sparepart & Items | Penambahan sparepart dan snapshot harga | Service order aktif, stok sparepart mencukupi | 1. POST `/api/v1/service-orders/{id}/items`<br>2. Pilih sparepart dan kuantitas | Stok sparepart berkurang, harga di-snapshot di `service_order_items` | Stok berkurang, subtotal dan grand total dihitung ulang | **PASS** | `PriceSnapshotTest::test_9_sparepart_dengan_stok_cukup_berhasil` | — |
| **TC-SPAR-02** | Sparepart & Items | Penolakan sparepart jika stok habis | Stok sparepart = 0 | 1. Tambahkan sparepart melebihi stok tersedia | Ditolak dengan pesan validasi stok tidak mencukupi | Status 422, stok tidak berkurang | **PASS** | `PriceSnapshotTest::test_10_sparepart_dengan_stok_tidak_cukup_ditolak` | — |
| **TC-PAY-01** | Payment & Invoice | Pembuatan transaksi pembayaran oleh customer | Service order berstatus `completed` | 1. POST `/api/v1/service-orders/{id}/payments`<br>2. Unggah bukti transfer | Payment berstatus `pending`, total tagihan dihitung dari data server | Payment tersimpan, nominal client-side diabaikan | **PASS** | `PaymentApiTest::test_customer_dapat_membuat_pembayaran` | — |
| **TC-PAY-02** | Admin Verification | Verifikasi pembayaran oleh Admin | Payment berstatus `pending` | 1. POST `/api/v1/payments/{id}/verify` oleh Admin | Status payment `verified`, booking berstatus `completed`, `paid_at` terisi | Status verified, notifikasi terkirim | **PASS** | `PaymentApiTest::test_admin_dapat_memverifikasi_pembayaran` | — |
| **TC-PAY-03** | Admin Verification | Penolakan verifikasi pembayaran oleh non-admin | User login sebagai Customer/Mekanik | 1. POST `/api/v1/payments/{id}/verify` | Sistem menolak akses dengan status 403 Forbidden | Status 403 Forbidden | **PASS** | `PaymentApiTest::test_non_admin_tidak_dapat_memverifikasi` | — |
| **TC-INV-01** | Payment & Invoice | Pembuatan dan akses invoice resmi | Service order selesai | 1. GET `/api/v1/invoices/{order_id}` | Menampilkan rincian jasa, sparepart, ongkir, total, dan status bayar | Data invoice lengkap terambil | **PASS** | `InvoiceApiTest::test_customer_dapat_melihat_invoice_miliknya` | — |
| **TC-DELV-01** | Pickup & Delivery | Pembuatan dan penugasan delivery task | Booking dengan opsi pickup/delivery | 1. POST `/api/v1/delivery-tasks/{id}/assign` oleh Admin | Task terhubung ke kurir, status `assigned` | Kurir ditugaskan, tarif snapshot terkunci | **PASS** | `DeliveryApiTest::test_admin_dapat_assign_courier` | — |
| **TC-DELV-02** | Pickup & Delivery | Isolasi tugas kurir | Kurir A mencoba complete task Kurir B | 1. POST `/api/v1/delivery-tasks/{id_B}/complete` oleh Kurir A | Sistem menolak akses dengan status 403 Forbidden | Status 403 Forbidden | **PASS** | `DeliveryApiTest::test_courier_lain_tidak_dapat_complete_task` | — |
| **TC-UI-01** | UI/UX & Responsive | Responsivitas tampilan navigasi dan form | Akses via desktop dan viewport mobile | 1. Navigasi menu dashboard per role<br>2. Verifikasi form booking dan transaksi | Layout responsive, elemen adaptif tanpa horizontal overflow | Seluruh Blade views menggunakan Tailwind responsive utilities | **PASS** | Visual & Blade Template Audit | — |
| **TC-NAV-01** | Navigation | Navigasi menu berdasarkan role pengguna | Akun Customer, Mekanik, Kurir, Admin | 1. Login masing-masing role<br>2. Periksa menu yang ditampilkan | Setiap role hanya melihat menu yang relevan (RBAC view level) | Menu terisolasi sesuai permission | **PASS** | `DashboardTest::role_based_navigation_audit` | — |
| **TC-INT-01** | Frontend-Backend | Integrasi form pemesanan dengan API / Controller | User customer di browser | 1. Submit form booking web<br>2. Redirect ke daftar booking dengan flash notification | Flash session "Booking berhasil dibuat", redirect 302 | Data terinput di DB, notifikasi muncul di UI | **PASS** | `EndToEndBusinessFlowTest::test_flow_1_service_biasa` | — |
| **TC-API-01** | Error Handling | Error handling 404 pada resource tidak ditemukan | Request ke ID acak | 1. GET `/api/v1/vehicles/999999` | Mengembalikan JSON konsisten dengan status 404 Not Found | JSON `{"success":false, "message":"..."}` 404 | **PASS** | `VehicleApiTest::test_vehicle_tidak_ditemukan_404` | — |
| **TC-SEC-01** | Security Checks | Proteksi Mass Assignment & Price Tampering | Manipulasi payload price pada request | 1. Kirim payload harga murah pada penambahan sparepart | Server mengabaikan payload harga client dan menggunakan harga master | Subtotal dihitung murni dari DB | **PASS** | `PriceSnapshotTest::test_4_manipulasi_price_snapshot_diabaikan` | — |
| **TC-REG-01** | Regression Testing | Pengujian regresi menyeluruh seluruh suite test | Lingkungan staging lokal | 1. Eksekusi `php artisan test` | Seluruh 340 test lulus tanpa kegagalan | **340 passed (801 assertions)** | **PASS** | `Automated Test Execution Log` | — |

---

## 3. Kesimpulan QA/QC

Seluruh skenario pengujian bisnis (17 modul) telah terverifikasi dengan tingkat kelulusan **100%**. Seluruh celah keamanan esensial seperti *Price Tampering*, *Cross-Tenant Data Leakage*, dan *Unauthorized Escalation* terbukti terlindungi dengan baik oleh policy, service layer, dan middleware Laravel.

