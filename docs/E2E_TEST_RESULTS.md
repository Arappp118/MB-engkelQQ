# Hasil Pengujian End-to-End (E2E) Alur Bisnis — MotoCare

Dokumen ini mendokumentasikan hasil pengujian integrasi alur bisnis secara end-to-end yang diimplementasikan pada [`tests/Feature/EndToEndBusinessFlowTest.php`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/tests/Feature/EndToEndBusinessFlowTest.php) dan dieksekusi secara nyata pada lingkungan pengujian terisolasi.

---

## 1. Flow 1: Service Biasa (Reguler) End-to-End

* **Metode Test:** `test_flow_1_service_biasa_end_to_end`
* **Aktor:** Customer (`customer`), Mekanik (`mechanic`), Admin (`admin`)
* **Status:** **PASS** (15 Assertions)

### Langkah-langkah Eksekusi & Pembuktian:
1. **Inisialisasi Master:** Admin menyiapkan master sparepart `Oli Mesin MPX 0.8L` (stok: 10, harga: Rp 55.000) dan jasa `Jasa Ganti Oli` (harga: Rp 20.000).
2. **Registrasi Kendaraan:** Customer membuat kendaraan Honda Vario 125 nopol `B 1234 ETE`.
3. **Reservasi Booking:** Customer membuat booking servis rutin. Status awal booking: `pending`.
4. **Konfirmasi Admin & Assignment:** Admin mengonfirmasi booking. Mekanik dengan spesialisasi mesin 4-tak ditugaskan secara otomatis.
5. **Mulai Pengerjaan Servis:** Mekanik mengubah status order servis menjadi `in_progress`.
6. **Pencatatan Diagnosis:** Mekanik mengisi diagnosis: `"Oli sudah hitam dan perlu diganti segera."`.
7. **Penggunaan Sparepart & Jasa:** Mekanik menambahkan 1 botol oli dan jasa.
   - *Verifikasi Stok:* Stok sparepart berkurang dari 10 menjadi 9 unit secara atomik.
   - *Verifikasi Grand Total:* Dihitung otomatis oleh server: Rp 55.000 + Rp 20.000 = Rp 75.000.
8. **Penyelesaian Servis:** Mekanik menyelesaikan servis; status order menjadi `completed`.
9. **Pengecekan Invoice:** Customer melihat invoice Rp 75.000 dengan status belum bayar.
10. **Pengajuan Pembayaran:** Customer mengunggah bukti transfer:
    - *Anti-Tamper:* Payload manipulasi nominal (`amount: 1000`) diabaikan oleh server; nominal payment tetap Rp 75.000.
    - *Lifecycle:* Status pembayaran menjadi `waiting_verification`, kolom `paid_at` bernilai `NULL`.
11. **Verifikasi Admin:** Admin memverifikasi pembayaran.
    - Status pembayaran menjadi `verified`, `paid_at` terisi timestamp aktual `now()`.
    - Status booking beralih menjadi `completed`.
12. **Status Akhir Invoice:** Invoice menampilkan badge status `LUNAS` dan waktu pelunasan.

---

## 2. Flow 2: Service dengan Layanan Antar-Jemput (Pickup & Delivery)

* **Metode Test:** `test_flow_2_pickup_and_delivery_end_to_end`
* **Aktor:** Customer, Kurir (`courier`), Mekanik, Admin
* **Status:** **PASS** (13 Assertions)

### Langkah-langkah Eksekusi & Pembuktian:
1. **Konfigurasi Tarif Antar-Jemput:** Tarif dasar diset Rp 10.000, tarif per km Rp 2.000.
2. **Booking Antar-Jemput:** Customer membuat booking dengan `pickup_requested = true` (jarak estimasi 5 km).
3. **Penjemputan Kurir:**
   - Tugas pickup dibuat dan dialokasikan ke kurir.
   - Kurir memulai tugas (`in_progress`) lalu menyelesaikannya (`completed`).
4. **Kedatangan di Bengkel:** Kendaraan tiba di bengkel, status booking beralih ke `in_service`.
5. **Servis oleh Mekanik:** Mekanik mendiagnosis, melampirkan item servis, dan menyelesaikan pengerjaan.
6. **Pembuatan Tugas Pengantaran:** Tugas kurir tipe `delivery` dibuat untuk mengantarkan motor kembali ke rumah customer.
7. **Pembayaran & Transisi Sah:** Customer membayar tagihan. Saat admin memverifikasi pembayaran:
   - *Validasi Bisnis:* Booking **TIDAK dipaksa selesai prematur**, melainkan beralih ke status `ready_for_delivery` karena tugas pengantaran masih aktif.
8. **Pengantaran oleh Kurir:** Kurir memulai (`start`) dan menyelesaikan (`complete`) tugas delivery.
9. **Penyelesaian Akhir:** Status booking secara sah beralih ke `completed` setelah motor diterima customer.

---

## 3. Flow 3: Metode Pembayaran (Reject & Re-submit, Cash, QRIS)

* **Metode Test:** `test_flow_3_payment_cash_transfer_rejection_and_qris`
* **Aktor:** Customer, Admin
* **Status:** **PASS** (9 Assertions)

### Langkah-langkah Eksekusi & Pembuktian:
1. **Pengajuan Bukti Buram:** Customer mengirim bukti transfer buram. Status: `waiting_verification`.
2. **Penolakan oleh Admin:** Admin menolak pembayaran dengan alasan: `"Bukti buram dan mutasi bank belum masuk"`.
   - Status pembayaran berubah menjadi `rejected`.
   - Alasan penolakan tercatat pada kolom `notes` pembayaran dan audit log.
3. **Pengajuan Ulang (Re-submission):** Customer mengunggah file bukti baru yang jelas pada order servis yang sama.
   - Sistem menerbitkan record pembayaran baru yang terisolasi dari pembayaran yang ditolak.
   - Status kembali menjadi `waiting_verification`.
4. **Verifikasi Sukses:** Admin memverifikasi pembayaran kedua; order dan invoice sukses lunas.

---

## 4. Flow 4: Keamanan & Integritas Data (IDOR, Tampering, Snapshot)

* **Metode Test:** `test_flow_4_security_idor_and_tampering_integrity`
* **Aktor:** Customer A, Customer B, Mekanik, Kurir
* **Status:** **PASS** (10 Assertions)

### Langkah-langkah Eksekusi & Pembuktian:
1. **IDOR Kendaraan:** Customer B ditolak (HTTP 403) saat mencoba mengakses kendaraan Customer A.
2. **IDOR Booking:** Customer B ditolak (HTTP 403) saat mencoba melihat booking Customer A.
3. **IDOR Invoice:** Customer B ditolak (HTTP 403) saat mencoba melihat invoice Customer A.
4. **IDOR Pembayaran:** Customer B ditolak (HTTP 403) saat mencoba mengajukan pembayaran untuk pesanan Customer A.
5. **Eskalasi Peran Staf:**
   - Mekanik ditolak (HTTP 403) saat mencoba memverifikasi pembayaran.
   - Kurir ditolak (HTTP 403) saat mencoba menolak pembayaran.
6. **Integritas Snapshot Harga:** Master harga barang diubah dari Rp 30.000 menjadi Rp 50.000. Snapshot harga pada order item lama tetap bertahan pada Rp 30.000 (*immutable*).
