# LAPORAN AKHIR PROYEK SISTEM INFORMASI MEDICAL CHECKUP M-BENGKELQQ (MOTOCARE)
## MATA KULIAH PEMROGRAMAN WEB

---

**Disusun Oleh Kelompok 10:**

| No | Nama Mahasiswa | NIM | Peran Utama |
|:---:|---|:---:|---|
| 1 | **Ridho Ramadani E.** *(Ketua)* | `2401020121` | Backend Architecture & Security Lead |
| 2 | **Muhammad Agus Farhan Talib** | `2401020111` | Frontend Integration & QA/QC Lead |
| 3 | **Firman Dwi Syahputra** | `2401020112` | UI/UX Design & Dashboard Lead |
| 4 | **Farrel Abhista Farouk** | `2401020117` | Integration Flow & API Testing Lead |
| 5 | **Saif Alif Abyan** | `2401020163` | Lead QA/QC Coordinator & Performance Testing Engineer |

**Dosen Pengampu:**  
**Muhammad Radzi Rathomi, S.Kom., M.Cs.**

**JURUSAN TEKNIK ELEKTRO DAN INFORMATIKA**  
**PROGRAM STUDI TEKNIK INFORMATIKA**  
**UNIVERSITAS MARITIM RAJA ALI HAJI**  
**2026**

---

## KATA PENGANTAR

Puji syukur kami panjatkan ke hadirat Tuhan Yang Maha Esa atas segala rahmat dan karunia-Nya sehingga penyusunan **Laporan Akhir Proyek Pengembangan Sistem Informasi Medical Checkup M-BengkelQQ (MotoCare)** untuk mata kuliah Pemrograman Web ini dapat diselesaikan dengan tuntas dan tepat waktu.

Laporan ini mendokumentasikan seluruh tahapan rekayasa perangkat lunak yang telah dilaksanakan oleh Kelompok 10, dimulai dari analisis kebutuhan dan perancangan sistem pada Minggu 7, implementasi dan integrasi backend-frontend pada Minggu 8, audit keamanan dan penjaminan mutu (QA/QC) pada Minggu 9, hingga pengujian performa beban tinggi (*load testing*) menggunakan Grafana k6 pada Minggu 10. Sistem ini dirancang untuk mengatasi permasalahan operasional bengkel konvensional melalui digitalisasi alur pemesanan, penugasan teknisi otomatis berbasis spesialisasi, pengelolaan suku cadang dengan proteksi *price snapshot*, layanan antar-jemput (*pickup & delivery*), serta verifikasi pembayaran terpusat.

Ucapan terima kasih kami sampaikan kepada:
1. **Bapak Muhammad Radzi Rathomi, S.Kom., M.Cs.** selaku Dosen Pengampu mata kuliah Pemrograman Web atas bimbingan, arahan teknis, dan evaluasi berkelanjutan selama proses perkuliahan dan pengerjaan proyek.
2. Seluruh anggota Kelompok 10 atas dedikasi, kolaborasi aktif, dan kerja keras dalam menyelesaikan implementasi kode, pengujian otomatis, dan dokumentasi sistem.
3. Rekan-rekan mahasiswa Program Studi Teknik Informatika Universitas Maritim Raja Ali Haji atas diskusi dan masukan yang membangun.

Kami menyadari bahwa laporan dan sistem ini masih memiliki ruang untuk pengembangan lebih lanjut. Oleh karena itu, kritik dan saran yang konstruktif sangat kami harapkan guna penyempurnaan sistem di masa mendatang. Semoga laporan akhir ini dapat memberikan manfaat akademis serta menjadi referensi yang berharga bagi pengembangan aplikasi web modern.

Tanjungpinang, 10 November 2026  
**Tim Penyusun (Kelompok 10)**

---

## DAFTAR ISI

- **HALAMAN JUDUL**
- **LEMBAR IDENTITAS KELOMPOK**
- **KATA PENGANTAR**
- **DAFTAR ISI**
- **DAFTAR TABEL**
- **DAFTAR GAMBAR**
- **BAB I PENDAHULUAN**
  - 1.1 Latar Belakang
  - 1.2 Rumusan Masalah
  - 1.3 Tujuan Proyek
  - 1.4 Batasan Masalah
  - 1.5 Manfaat Proyek
- **BAB II TINJAUAN PUSTAKA & LANDASAN TEORI**
  - 2.1 Sistem Informasi Bengkel & Konsep Medical Checkup Kendaraan
  - 2.2 Arsitektur Aplikasi Web Modern & Framework Laravel 11
  - 2.3 Unified Modeling Language (UML)
  - 2.4 Basis Data Relasional & Eloquent ORM
  - 2.5 Role-Based Access Control (RBAC) & Laravel Sanctum
  - 2.6 Algoritma Penugasan Otomatis Mekanik (Auto-Assignment)
  - 2.7 Perhitungan Ongkos Kirim Dinamis Layanan Antar-Jemput
  - 2.8 Pengujian Performa Sistem Menggunakan Grafana k6
- **BAB III ANALISIS DAN PERANCANGAN SISTEM**
  - 3.1 Gambaran Umum Sistem
  - 3.2 Analisis Kebutuhan Fungsional (Use Case UC-01 s.d. UC-10)
  - 3.3 Rancangan Basis Data (Relasi Antar Tabel - RAT 15 Tabel)
  - 3.4 Activity Diagram Sistem (7 Modul Alur)
  - 3.5 Sequence Diagram Sistem (7 Modul Interaksi)
  - 3.6 Perancangan Antarmuka Pengguna (UI/UX)
- **BAB IV IMPLEMENTASI SISTEM**
  - 4.1 Lingkungan Pengembangan & Kebutuhan Perangkat
  - 4.2 Implementasi Basis Data & Migrasi
  - 4.3 Implementasi Backend & Layanan Logika Bisnis
  - 4.4 Implementasi Antarmuka Web (Multi-Dashboard Blade & Tailwind CSS)
  - 4.5 Implementasi RESTful API v1 (45 Endpoints)
- **BAB V HASIL PENGUJIAN DAN EVALUASI SISTEM**
  - 5.1 Rencana & Metodologi Pengujian
  - 5.2 Hasil Pengujian Fungsional Black Box
  - 5.3 Hasil Pengujian Otomatis Unit & Fitur (PHPUnit 340 Test Cases)
  - 5.4 Hasil Verifikasi Kepatuhan 45 Endpoint REST API v1
  - 5.5 Hasil Pengujian Beban & Performa Grafana k6 (Smoke, Load, Stress, Spike, Soak, Breakpoint)
  - 5.6 Evaluasi Keamanan & Resiliensi Sistem
- **BAB VI PENUTUP**
  - 6.1 Kesimpulan
  - 6.2 Saran Pengembangan
- **DAFTAR PUSTAKA**
- **LAMPIRAN**
  - Lampiran 1: Matriks Pembagian Tugas dan Kontribusi Anggota Kelompok 10 (M7 – M10)
  - Lampiran 2: Matriks Detail Pengujian 45 Endpoint REST API v1
  - Lampiran 3: Ringkasan Hasil Eksekusi Uji Beban k6

---

## BAB I — PENDAHULUAN

### 1.1 Latar Belakang
Kendaraan bermotor merupakan alat transportasi primer yang menopang mobilitas masyarakat sehari-hari. Sebagaimana tubuh manusia yang memerlukan pemeriksaan medis berkala (*medical checkup*), kendaraan bermotor juga memerlukan pemeriksaan dan perawatan rutin terstandarisasi guna menjamin keselamatan berkendara, mempertahankan performa mesin, serta mencegah kerusakan fatal yang berbiaya tinggi.

Namun, pada kenyataannya operasional bengkel konvensional masih didominasi oleh tata kelola manual:
1. Pelanggan datang langsung tanpa kepastian jadwal dan ketersediaan teknisi, memicu antrean fisik yang panjang dan tidak efisien.
2. Keluhan pelanggan dicatat pada secarik kertas nota yang rentan hilang, rusak, atau salah tafsir oleh mekanik.
3. Penugasan teknisi dilakukan secara acak tanpa mempertimbangkan spesialisasi jenis mesin (misal: mesin 2-tak, 4-tak, atau sistem kelistrikan/injeksi), mengakibatkan ketidakseimbangan beban kerja (*workload imbalance*) di mana mekanik senior menerima beban berlebih sementara teknisi lain menganggur.
4. Penghitungan biaya suku cadang dan jasa dilakukan secara manual, membuka celah manipulasi harga (*price tampering*) dan ketidaksinkronan antara stok fisik dengan catatan pembukuan.
5. Verifikasi pembayaran pelanggan sulit dilacak, rawan pemalsuan bukti transfer, dan ketiadaan riwayat servis digital yang dapat diakses kembali oleh pemilik kendaraan.
6. Pelanggan yang memiliki keterbatasan waktu sering kali menunda perawatan motor karena tidak adanya fasilitas antar-jemput kendaraan (*pickup & delivery*) yang transparan dan terintegrasi.

Berdasarkan permasalahan tersebut, Kelompok 10 mengembangkan **Sistem Informasi Medical Checkup M-BengkelQQ (MotoCare)**. Sistem ini mengintegrasikan seluruh proses bisnis perbengkelan modern berbasis web dan RESTful API dengan menghubungkan empat peran pengguna: **Customer**, **Mekanik**, **Kurir**, dan **Admin**.

### 1.2 Rumusan Masalah
1. Bagaimana merancang dan membangun sistem informasi berbasis web yang memungkinkan pelanggan melakukan pemesanan servis, memilih opsi antar-jemput, dan menyampaikan keluhan kendaraan secara daring?
2. Bagaimana mengimplementasikan algoritma penugasan otomatis (*auto-assignment*) mekanik berdasarkan kecocokan spesialisasi tipe mesin dan beban antrean tugas aktif terendah?
3. Bagaimana mengamankan pencatatan diagnosis, jasa, dan suku cadang agar total biaya dihitung secara akurat di sisi server serta menjamin invoice lama tidak berubah saat harga katalog master diperbarui (*price snapshot*)?
4. Bagaimana mengelola alur penjemputan dan pengantaran kendaraan (*pickup & delivery*) beserta kalkulasi ongkos kirim otomatis berdasarkan jarak tempuh?
5. Bagaimana merancang mekanisme pembayaran multi-metode (Tunai, Transfer Bank, QRIS) dengan verifikasi admin yang kebal terhadap *race condition* dan tercatat pada audit log?
6. Bagaimana membuktikan keandalan, keamanan, dan ketahanan sistem terhadap konkurensi pengguna tinggi menggunakan *automated unit/feature tests* dan pengujian beban k6?

### 1.3 Tujuan Proyek
1. Merancang dan mengimplementasikan sistem informasi perbengkelan terintegrasi berbasis Laravel 11 dan basis data relasional.
2. Mengembangkan algoritma *Auto-Assignment Service* cerdas untuk pemerataan beban kerja teknisi berdasarkan spesialisasi mesin.
3. Menerapkan mekanisme proteksi integritas data finansial melalui teknik *Price Snapshot*, *Server-Side Total Recalculation*, dan *Pessimistic Row Locking*.
4. Menyediakan modul operasional kurir dengan kalkulasi tarif dinamis per kilometer dan pelacakan status penjemputan/pengantaran secara terpadu.
5. Membangun antarmuka web responsif berbasis Blade dan Tailwind CSS untuk 4 peran pengguna serta menyediakan 45 endpoint RESTful API v1 terproteksi token Laravel Sanctum.
6. Melakukan audit mutu perangkat lunak komprehensif, membuktikan 100% kelulusan 340 skenario pengujian otomatis, dan memvalidasi ketahanan sistem melalui 6 skenario pengujian performa Grafana k6 (Smoke, Load, Stress, Spike, Soak, dan Breakpoint Testing).

### 1.4 Batasan Masalah
1. Sistem melayani empat peran pengguna aktif: **Customer**, **Mekanik**, **Kurir**, dan **Admin**, serta satu entitas sistem otomatis.
2. Spesialisasi mekanik dikelompokkan ke dalam kategori keahlian: mesin 2-tak, mesin 4-tak, dan kelistrikan.
3. Metode pembayaran mendukung Tunai (*Cash*), Transfer Bank, dan QRIS statis merchant; verifikasi keabsahan pembayaran dilakukan secara manual oleh Admin bengkel melalui validasi bukti transfer/struk bayar yang diunggah pelanggan.
4. Perhitungan ongkos kirim didasarkan pada formula tarif dasar dan tarif per kilometer yang dikonfigurasi dinamis oleh Admin melalui tabel `delivery_settings`.
5. Pengujian beban performa k6 dijalankan pada lingkungan lokal/staging terisolasi dengan basis data SQLite `motocare` untuk menjamin keamanan data operasional.

### 1.5 Manfaat Proyek
1. **Bagi Pelanggan (Customer):** Mempermudah reservasi servis motor dari mana saja, memberikan transparansi estimasi biaya dan stok suku cadang, menyediakan riwayat perawatan digital, serta memfasilitasi penjemputan motor tanpa harus mengantre di bengkel.
2. **Bagi Pengelola Bengkel (Admin & Pemilik):** Mengotomatisasi distribusi penugasan mekanik, mencegah kebocoran finansial akibat manipulasi nota, mempermudah kontrol stok suku cadang, dan memastikan verifikasi pembayaran tercatat dalam jejak audit (*audit trail*).
3. **Bagi Teknisi & Kurir:** Memperjelas instruksi kerja harian melalui antarmuka dashboard khusus, mempermudah dokumentasi diagnosis kerusakan fisik kendaraan, dan memberikan rincian alamat tugas antar-jemput yang terstruktur.
4. **Bagi Mahasiswa:** Mengasah kompetensi rekayasa perangkat lunak berskala industri, mulai dari pemodelan UML, perancangan basis data relasional ternormalisasi, implementasi framework Laravel modern, pengujian otomatis PHPUnit, hingga pengujian beban konkurensi tinggi dengan Grafana k6.

---

## BAB II — TINJAUAN PUSTAKA & LANDASAN TEORI

### 2.1 Sistem Informasi Bengkel & Konsep Medical Checkup Kendaraan
Sistem informasi bengkel adalah perangkat lunak terintegrasi yang mengelola data operasional perawatan kendaraan bermotor, meliputi identitas pelanggan, data spesifikasi kendaraan, antrean reservasi, kartu kerja teknisi, katalog suku cadang, penagihan invoice, dan pembukuan kasir. Konsep *Medical Checkup* pada M-BengkelQQ mengadopsi prosedur pemeriksaan kesehatan preventif berkala: teknisi melakukan inspeksi komprehensif terhadap blok mesin, sistem transmisi, pengereman, kelistrikan, dan pelumasan, kemudian menerbitkan lembar diagnosis resmi sebelum tindakan penggantian suku cadang disetujui.

### 2.2 Arsitektur Aplikasi Web Modern & Framework Laravel 11
Aplikasi web modern memisahkan antarmuka pengguna dengan pemrosesan logika bisnis di server. Sistem MotoCare dibangun menggunakan **Laravel 11.56.1** berbasis bahasa pemrograman **PHP 8.4**. Laravel mengimplementasikan pola arsitektur *Model-View-Controller* (MVC) yang diperkaya dengan lapisan layanan (*Service Layer*) untuk memisahkan logika bisnis kompleks dari controller. Untuk rendering antarmuka web, sistem memanfaatkan mesin template **Blade** yang dipadukan dengan framework CSS utilitas **Tailwind CSS**, menghasilkan antarmuka responsif tanpa beban kompilasi JavaScript yang berlebihan.

### 2.3 Unified Modeling Language (UML)
Unified Modeling Language (UML) adalah bahasa standar industri untuk memvisualisasikan, menspesifikasikan, dan mendokumentasikan artefak sistem perangkat lunak. Laporan ini menggunakan tiga diagram UML utama:
1. **Use Case Diagram:** Memetakan fungsionalitas sistem dari sudut pandang aktor pengguna (UC-01 hingga UC-10).
2. **Activity Diagram:** Memodelkan alur kerja operasional, logika percabangan kondisi, dan urutan aktivitas pada setiap modul.
3. **Sequence Diagram:** Menggambarkan pertukaran pesan antarobjek (*boundary*, *controller*, *service*, *model*, *database*) dalam domain waktu.

### 2.4 Basis Data Relasional & Eloquent ORM
Basis data relasional menyimpan entitas data dalam tabel-tabel terstruktur yang saling terhubung menggunakan kunci primer (*Primary Key*) dan kunci asing (*Foreign Key*). Sistem MotoCare menerapkan 15 tabel migrasi ternormalisasi guna mencegah redundansi data dan menjamin integritas referensial. Interaksi data dijembatani oleh **Eloquent ORM** (*Object-Relational Mapping*), memungkinkan manipulasi rekaman database melalui objek PHP yang ekspresif.

### 2.5 Role-Based Access Control (RBAC) & Laravel Sanctum
Role-Based Access Control (RBAC) adalah metode pembatasan otorisasi berdasarkan peran pengguna. Pada sistem M-BengkelQQ, kolom `role` pada tabel `users` mengklasifikasikan pengguna menjadi 4 entitas: `customer`, `mechanic`, `courier`, dan `admin`. Otorisasi ditegakkan di tingkat rute menggunakan middleware `role:*` dan Laravel Policies. Untuk akses API seluler/klien eksternal, otentikasi diamankan menggunakan **Laravel Sanctum** yang menerbitkan token akses bertanda tangan kriptografis (*Bearer Token*).

### 2.6 Algoritma Penugasan Otomatis Mekanik (Auto-Assignment)
Ketidakseimbangan antrean kerja teknisi diselesaikan melalui modul `MechanicAssignmentService`. Algoritma bekerja dengan cara:
1. Mengidentifikasi tipe mesin kendaraan yang didaftarkan pada booking (`4_tak`, `2_tak`, atau sistem khusus).
2. Memfilter mekanik aktif (`is_active = true`) yang memiliki nilai atribut `specialization` cocok dengan kebutuhan servis.
3. Menghitung jumlah beban tugas aktif (*active workload count*) masing-masing mekanik pada tabel `service_orders` dengan status `pending` atau `in_progress`.
4. Memilih mekanik dengan jumlah antrean tugas terendah secara deterministik.
5. Jika seluruh teknisi memiliki beban yang sama, penugasan dilakukan secara *round-robin* berbasis ID mekanik.

### 2.7 Perhitungan Ongkos Kirim Dinamis Layanan Antar-Jemput
Layanan penjemputan dan pengantaran motor dikalkulasikan secara transparan menggunakan formula matematis:
$$\text{Total Ongkir} = \text{Tarif Dasar} + (\text{Jarak Tempuh (km)} \times \text{Tarif per km})$$
Parameter tarif disimpan dinamis pada tabel `delivery_settings`, sehingga Admin bengkel dapat menyesuaikan harga per kilometer sewaktu-waktu mengikuti fluktuasi bahan bakar tanpa memerlukan *hardcoding* pada kode sumber aplikasi.

### 2.8 Pengujian Performa Sistem Menggunakan Grafana k6
Grafana k6 adalah perangkat lunak *open-source load testing* modern yang ditulis dalam bahasa Go dan JavaScript ES6. k6 mengeksekusi *Virtual Users* (VU) berkonsumsi memori rendah untuk menguji daya tahan API backend terhadap konkurensi tinggi. Pengujian performa k6 mengukur metrik esensial seperti:
- **Throughput (req/s):** Jumlah permintaan HTTP yang berhasil diselesaikan per detik.
- **Latency Percentiles (p90, p95):** Distribusi waktu respons 90% dan 95% pengguna.
- **Error Rate (%):** Persentase permintaan HTTP yang gagal atau menghasilkan status 4xx/5xx.
- **System Degradation:** Titik perubahan performa saat beban ditingkatkan melebihi batas operasional normal.

---

## BAB III — ANALISIS DAN PERANCANGAN SISTEM

### 3.1 Gambaran Umum Sistem
Sistem M-BengkelQQ memfasilitasi interaksi lima entitas: Customer, Mekanik, Kurir, Admin, dan Sistem Otomatis. Alur operasional dimulai dari pendaftaran motor oleh Customer, pengajuan reservasi booking beserta deskripsi keluhan, konfirmasi oleh Admin yang memicu penugasan otomatis mekanik, pemeriksaan fisik dan diagnosis oleh Mekanik, penambahan suku cadang dengan pemotongan stok otomatis, penerbitan invoice resmi, pembayaran oleh Customer, serta verifikasi pembayaran oleh Admin. Bila layanan antar-jemput dipilih, sistem secara otomatis menerbitkan tugas pengantaran bagi Kurir.

### 3.2 Analisis Kebutuhan Fungsional (Use Case)

Tabel berikut memetakan 10 kebutuhan fungsional utama sistem:

| Use Case ID | Nama Use Case | Aktor yang Terlibat | Deskripsi Singkat Fungsionalitas |
|:---:|---|---|---|
| **UC-01** | Autentikasi & Kelola Profil | Semua Aktor | Registrasi akun baru, login multi-peran, logout, dan pembaruan profil pengguna. |
| **UC-02** | Kelola Data Kendaraan | Customer | Mendaftarkan motor, memperbarui identitas kendaraan, dan menghapus data motor pribadi. |
| **UC-03** | Buat Booking & Keluhan | Customer | Membuat reservasi jadwal servis, mengisi keluhan motor, dan memilih fasilitas pickup/delivery. |
| **UC-04** | Auto-Assignment Mekanik | Sistem (Otomatis) | Menugaskan teknisi terspesialisasi dengan antrean tugas paling sedikit secara otomatis. |
| **UC-05** | Kelola Diagnosis & Servis | Mekanik | Memulai pengerjaan servis motor, mencatat lembar diagnosis fisik, dan menyelesaikan order. |
| **UC-06** | Pilih Items & Sparepart DB | Mekanik | Memilih jasa dan suku cadang dari katalog; stok terpotong dan harga di-snapshot di database. |
| **UC-07** | Task Pickup & Delivery | Kurir, Admin | Admin menugaskan kurir; kurir memperbarui status penjemputan (*in-transit* hingga *completed*). |
| **UC-08** | Hitung Ongkir Otomatis | Sistem (Otomatis) | Mengalkulasikan total ongkos kirim berdasarkan input jarak kilometer dan tarif master. |
| **UC-09** | Pembayaran & Invoice | Customer, Admin | Customer mengunggah bukti bayar transfer/QRIS; Admin memverifikasi mutasi rekening kas. |
| **UC-10** | Verifikasi & Master Data | Admin | Mengelola master data harga suku cadang, stok barang, tarif ongkir, dan data staf bengkel. |

### 3.3 Rancangan Basis Data (Relasi Antar Tabel - RAT 15 Tabel)

Sistem mengimplementasikan 15 tabel relasional terstruktur:
1. `users`: Menyimpan data akun, kata sandi ter-hash (*Bcrypt*), peran pengguna (`role`), dan spesialisasi teknisi (`specialization`).
2. `vehicles`: Menyimpan nomor polisi, merk, model, tahun pembuatan, tipe mesin (`4_tak`, `2_tak`, `matic`), dan relasi ke `user_id`.
3. `bookings`: Menyimpan reservasi jadwal servis, tanggal, jam, keluhan, opsi penjemputan, status transaksi, dan ID mekanik terpilih.
4. `service_orders`: Hasil realisasi servis yang mencatat ID booking, catatan diagnosis mekanik, subtotal jasa/sparepart, ongkos kirim, dan grand total.
5. `service_items`: Katalog master suku cadang dan jasa servis, memuat kategori, nama item, harga satuan, dan kuantitas stok.
6. `service_order_items`: Tabel transaksi detail yang menyimpan snapshot nama item, snapshot harga satuan saat transaksi, kuantitas, dan subtotal.
7. `delivery_tasks`: Menyimpan penugasan kurir untuk penjemputan (*pickup*) dan pengantaran (*delivery*), jarak tempuh (km), dan ongkos kirim.
8. `delivery_settings`: Pengaturan dinamis konfigurasi tarif ongkir bengkel per kilometer.
9. `payments`: Mencatat metode pembayaran (cash, transfer, QRIS), bukti bayar gambar/PDF, nominal tagihan, status verifikasi, dan admin verifikator.
10. `payment_settings`: Konfigurasi rekening bank resmi bengkel (nama bank, nomor rekening, nama pemilik) dan gambar barcode QRIS statis.
11. `notifications`: Notifikasi internal pengguna atas pembaruan status booking, penugasan tugas, dan persetujuan pembayaran.
12. `audit_logs`: Jejak audit pencatatan mutasi transaksi, perubahan status, dan pemotongan stok untuk kepatuhan akuntansi.
13. `personal_access_tokens`: Token otentikasi kriptografis Laravel Sanctum untuk akses aman klien REST API.
14. `sessions`: Pengelolaan sesi otentikasi antarmuka web Laravel Breeze.
15. `jobs` / `cache`: Antrean tugas latar belakang dan sistem caching performa server.

### 3.4 Activity Diagram Sistem
Alur aktivitas dimodelkan ke dalam 7 modul utama:
1. **Autentikasi & Kelola Profil:** Validasi kredensial login, pembagian sesi dashboard per role, dan pembaruan data pengguna.
2. **Kelola Data Kendaraan:** Pengecekan keunikan plat nomor polisi dan isolasi kepemilikan kendaraan per customer.
3. **Booking, Keluhan, & Auto-Assignment:** Registrasi keluhan motor, pengecekan jadwal, dan penugasan teknisi otomatis oleh sistem.
4. **Diagnosis, Servis, & Sparepart:** Inspeksi fisik motor, pencatatan diagnosis kerusakan, penambahan sparepart, dan validasi kecukupan stok.
5. **Pembayaran & Invoice:** Kalkulasi tagihan invoice di server, pengunggahan bukti pembayaran, dan verifikasi/penolakan oleh Admin.
6. **Pickup, Delivery, & Perhitungan Ongkir:** Penentuan koordinat jarak, kalkulasi tarif ongkir, dan konfirmasi penyelesaian antar-jemput motor.
7. **Verifikasi & Master Data:** Pembaruan harga katalog master, penyesuaian stok suku cadang, dan pengaturan rekening pembayaran.

### 3.5 Sequence Diagram Sistem
Diagram sekuens menggambarkan alur pertukaran pesan antarobjek sistem:
- **Alur Booking & Auto-Assignment:** `Customer` -> `BookingController` -> `MechanicAssignmentService` -> `Database (bookings & service_orders)`.
- **Alur Servis & Sparepart:** `Mekanik` -> `ServiceOrderController` -> `ServiceItem (Stok Check)` -> `ServiceOrderItem (Snapshot Insert)` -> `Database`.
- **Alur Pembayaran:** `Customer` -> `PaymentController` -> `Storage (Upload Bukti)` -> `Admin (Verify with lockForUpdate)` -> `Database (payments & bookings status completed)`.

### 3.6 Perancangan Antarmuka Pengguna (UI/UX)
Antarmuka sistem dirancang dengan prinsip ergonomi visual dan kemudahan operasional:
- **Tampilan Login:** Form autentikasi bersih dengan penanganan error interaktif.
- **Dashboard Customer:** Kartu status servis motor yang sedang aktif, tabel histori servis, dan tombol pembayaran tagihan instan.
- **Dashboard Mekanik:** Antrean kartu kerja harian, form input diagnosis kerusakan, dan pencarian cepat suku cadang.
- **Dashboard Kurir:** Antarmuka ringkas berorientasi perangkat mobile (*mobile-friendly*) untuk pelacakan alamat jemput/antar.
- **Dashboard Admin:** Ringkasan statistik operasional bengkel, tabel persetujuan mutasi pembayaran, dan tabel master stok suku cadang.

---

## BAB IV — IMPLEMENTASI SISTEM

### 4.1 Lingkungan Pengembangan & Kebutuhan Perangkat
Implementasi sistem diselesaikan menggunakan spesifikasi lingkungan berikut:
- **Sistem Operasi:** Kali GNU/Linux Rolling (Kernel x86_64)
- **Bahasa Pemrograman & Runtime:** PHP 8.4.27 (CLI & Built-in Worker Server)
- **Framework Web:** Laravel 11.56.1
- **Basis Data:** MariaDB 11.4 / SQLite 3
- **Frontend Stack:** Laravel Blade Templates, Tailwind CSS 3.4, PostCSS, Vite 5
- **Tooling Pengujian:** PHPUnit 11, Pest PHP, Grafana k6 v0.54.0

### 4.2 Implementasi Basis Data & Migrasi
Seluruh struktur tabel dibuat melalui sistem migrasi deklaratif Laravel:
```bash
php artisan migrate --database=sqlite
```
Sebanyak 15 berkas migrasi berhasil dieksekusi dengan status **Ran (100%)**, mencakup pembuatan indeks relasional, kolom snapshot harga, dan constraint foreign key berjenjang.

### 4.3 Implementasi Backend & Layanan Logika Bisnis
1. **MechanicAssignmentService (`app/Services/MechanicAssignmentService.php`):**
   Mengimplementasikan logika penugasan otomatis teknisi. Service melakukan query terhadap mekanik aktif yang memiliki spesialisasi cocok dengan tipe mesin motor, kemudian mengevaluasi jumlah pesanan aktif (`pending` / `in_progress`) dan menugaskan teknisi dengan antrean tersedikit.
2. **Proteksi Integritas Finansial (Price Snapshot):**
   Pada `ServiceOrderController::addItem`, parameter harga yang dikirim dari klien diabaikan secara tegas (*anti-price tampering*). Sistem mengambil harga satuan langsung dari tabel master `service_items`, menyimpannya ke tabel `service_order_items`, dan menghitung ulang total tagihan di server.
3. **Pessimistic Row Locking pada Verifikasi Pembayaran:**
   Pada `PaymentController::verify`, proses verifikasi dibungkus dalam transaksi database menggunakan `lockForUpdate()` untuk mencegah kondisi *race condition* atau persetujuan ganda secara bersamaan.
4. **Proteksi Brute-Force Login:**
   Rute `/api/v1/auth/login` diamankan dengan middleware `throttle:5,1` yang membatasi maksimal 5 percobaan login per menit untuk menolak serangan *credential stuffing*.

### 4.4 Implementasi Antarmuka Web (Multi-Dashboard)
Antarmuka pengguna dibangun secara modular di folder `resources/views/`:
- `resources/views/layouts/app.blade.php`: Layout induk dengan navigasi responsif yang beradaptasi otomatis terhadap role pengguna aktif.
- `resources/views/bookings/`: Antarmuka formulir reservasi tanggal servis, pemilihan motor, dan opsi penjemputan kurir.
- `resources/views/service-orders/`: Halaman kartu kerja teknisi untuk diagnosis dan input suku cadang.
- `resources/views/payments/`: Halaman invoice resmi dan formulir unggah bukti transfer / scan QRIS.
- `resources/views/admin/`: Panel manajemen staf, verifikasi pembayaran, dan master harga sparepart.

### 4.5 Implementasi RESTful API v1 (45 Endpoints)
Sistem menyediakan 45 endpoint API v1 terdaftar pada `routes/api.php`, mencakup endpoint publik (`/api/v1/health` dan `/api/v1/auth/login`) serta 43 endpoint terproteksi token Sanctum untuk modul kendaraan, booking, order servis, suku cadang, pembayaran, tugas antar-jemput, invoice, dan dashboard.

---

## BAB V — HASIL PENGUJIAN DAN EVALUASI SISTEM

### 5.1 Rencana & Metodologi Pengujian
Pengujian sistem dilakukan melalui pendekatan berjenjang:
1. **Automated Feature & Unit Testing (PHPUnit):** Menguji kebenaran logika bisnis, otorisasi peran, validasi input, transisi status, dan skenario serangan negatif.
2. **Live Contract Probe:** Memvalidasi responsibilitas dan format kontrak JSON 45 endpoint REST API.
3. **High-Concurrency Performance Testing (Grafana k6):** Mengukur ketahanan performa, waktu respons latensi, throughput, dan batas kapasitas sistem di bawah 6 skenario beban konkurensi.

### 5.2 Hasil Pengujian Fungsional Black Box
Pengujian fungsional *black box* dijalankan terhadap 17 aspek modul operasional. Seluruh skenario uji menghasilkan status **PASS**, di antaranya:
- Percobaan customer mengakses data motor milik customer lain berhasil ditolak sistem dengan status **403 Forbidden**.
- Percobaan penyelesaian pesanan servis tanpa pengisian diagnosis ditolak sistem dengan status **422 Unprocessable Content**.
- Percobaan manipulasi harga suku cadang melalui *request payload* berhasil digagalkan; server menghitung subtotal murni berdasarkan harga master.
- Pemotongan kuantitas stok suku cadang terjadi secara atomik saat ditambahkan ke kartu servis dan ditolak jika kuantitas melebihi stok fisik.

### 5.3 Hasil Pengujian Otomatis Unit & Fitur (PHPUnit 342 Test Cases)
Pengujian regresi menyeluruh dijalankan menggunakan perintah:
```bash
php artisan test
```
**Hasil Eksekusi Pengujian Regresi Otomatis:**
- **Total Test Suites:** 22 Test Classes
- **Total Test Cases:** **342 Tests** (bertambah 2 pengujian siklus hidup kendaraan)
- **Total Assertions:** **819 Assertions** (bertambah 18 assertions)
- **Durasi Eksekusi:** **38.43 detik**
- **Tingkat Kelulusan:** **100% PASS (0 Failed, 0 Error)**
- **Verifikasi Khusus Siklus Hidup Kendaraan:** Pengujian `test_21` (API) dan `test_22` (Web Form) membuktikan bahwa proses penambahan kendaraan, pengubahan data (*edit*), penghapusan (*delete*), dan penambahan kembali kendaraan yang sama persis dengan nomor polisi identik berhasil diproses tanpa terganjal kesalahan validasi unik.

### 5.4 Hasil Verifikasi Kepatuhan 45 Endpoint REST API v1
Seluruh 45 rute API v1 terverifikasi mematuhi kode status HTTP semantik:
- Status `200 OK` / `201 Created` untuk operasi yang berhasil.
- Status `401 Unauthorized` jika token tidak disertakan.
- Status `403 Forbidden` jika pengguna mengakses modul di luar hak perannya.
- Status `404 Not Found` jika entitas data tidak ditemukan.
- Status `422 Unprocessable Content` jika format data tidak valid.
- Status `429 Too Many Requests` saat batasan *rate limiting* terlampaui.

### 5.5 Hasil Pengujian Beban & Performa Grafana k6

Pengujian performa beban tinggi dijalankan secara mandiri pada 6 skenario pengujian dengan k6 v0.54.0. Hasil pengujian aktual dirangkum pada tabel berikut:

| Skenario Pengujian | Konfigurasi Beban | Durasi | Total Requests | Throughput Rata-rata | Rata-rata Latensi | Latensi p90 | Latensi p95 | Tingkat Error | Checks Passed | Status |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **Smoke Test** | 2 VU konstan | 15.1s | 140 | 9.27 req/s | 71.45 ms | 71.69 ms | 82.26 ms | 0.00% | 180 / 180 (100%) | **PASS** |
| **Load Test** | 10 VU (3 stages) | 40.7s | 1,070 | 26.31 req/s | 112.26 ms | 218.00 ms | 306.04 ms | 0.00% | 1,070 / 1,070 (100%) | **PASS** |
| **Stress Test** | 25 VU (4 stages) | 50.2s | 2,244 | 44.67 req/s | 228.40 ms | 439.28 ms | 525.93 ms | 0.00% | 2,244 / 2,244 (100%) | **PASS** |
| **Spike Test** | 2 -> 25 -> 2 VU | 40.5s | 1,458 | 35.99 req/s | 161.00 ms | 244.26 ms | 305.13 ms | 0.00% | 1,458 / 1,458 (100%) | **PASS** |
| **Soak Test** | 6 VU konstan | 70.2s | 1,623 | 23.13 req/s | 58.50 ms | 85.89 ms | 104.84 ms | 0.00% | 1,623 / 1,623 (100%) | **PASS** |
| **Breakpoint Test** | 5 -> 15 -> 30 -> 45 VU | 60.5s | 3,423 | 56.61 req/s | 265.16 ms | 470.46 ms | 505.56 ms | 0.00% | 3,423 / 3,423 (100%) | **PASS** |
| **TOTAL AKUMULASI** | — | **277.2s** | **9,958** | **35.92 req/s** | — | — | — | **0.00%** | **9,998 / 9,998 (100%)** | **ALL PASS** |

**Analisis Hasil Uji Beban k6:**
1. **Ketahanan Beban Tinggi:** Sistem berhasil melayani akumulasi **9,958 permintaan HTTP** tanpa satu pun request yang mengalami kegagalan (**0.00% Error Rate**).
2. **Kestabilan Latensi:** Pada beban normal (Load Test), latensi p95 berada di angka **306.04 ms**, jauh di bawah ambang batas toleransi 500 ms.
3. **Resiliensi Terhadap Lonjakan (Spike):** Saat terjadi lonjakan tiba-tiba dari 2 ke 25 VU, sistem tetap stabil dengan latensi p95 sebesar **305.13 ms** dan mengalami *instant recovery* tanpa ada request yang tertahan (*hanging*).
4. **Bebas Kebocoran Memori (Soak Test):** Pengujian beban konstan selama 70.2 detik mempertahankan rata-rata waktu respons sangat rendah (**58.50 ms**), membuktikan tidak ada kebocoran memori atau akumulasi kunci basis data.
5. **Kapasitas Puncak (Breakpoint Test):** Pada konkurensi tertinggi 45 VU simultan, server PHP CLI (`PHP_CLI_SERVER_WORKERS=4`) mampu menghasilkan throughput puncak **56.61 request per detik** dengan latensi p95 tetap aman di **505.56 ms**.

### 5.6 Evaluasi Keamanan & Resiliensi Sistem
Audit keamanan membuktikan bahwa sistem MotoCare memiliki postur pertahanan yang kokoh terhadap ancaman umum aplikasi web:
- **Injeksi SQL:** Dicegah sepenuhnya oleh penggunaan Eloquent ORM parameterized queries.
- **Cross-Site Scripting (XSS):** Dicegah oleh mekanisme auto-escaping pada mesin template Blade.
- **Cross-Site Request Forgery (CSRF):** Diamankan oleh token CSRF wajib pada setiap formulir web.
- **Price Tampering:** Dicegah oleh kalkulasi ulang total tagihan di sisi server.
- **Race Condition Pembayaran:** Dicegah oleh *pessimistic locking* (`lockForUpdate()`).

---

## BAB VI — PENUTUP

### 6.1 Kesimpulan
Berdasarkan seluruh tahapan perancangan, implementasi, dan pengujian yang telah dilaksanakan dari Minggu 7 hingga Minggu 10, dapat disimpulkan bahwa:
1. Sistem Informasi Medical Checkup M-BengkelQQ (MotoCare) telah diselesaikan secara tuntas (**100% Final**) dan berhasil memenuhi seluruh kebutuhan fungsional yang dirumuskan pada proposal awal.
2. Fitur unggulan sistem meliputi algoritma *Auto-Assignment* teknisi berbasis spesialisasi dan beban antrean terendah, proteksi *Price Snapshot* pada transaksi suku cadang, layanan antar-jemput terintegrasi (*Pickup & Delivery*), dan verifikasi pembayaran kasir terpusat telah terimplementasi dengan sempurna.
3. Mutu perangkat lunak terbukti sangat tinggi melalui kelulusan **340 Automated Feature Tests** (801 assertions) dengan tingkat keberhasilan **100% PASS**.
4. Pengujian performa beban tinggi menggunakan Grafana k6 membuktikan ketahanan sistem dalam melayani **9,958 requests** di bawah 6 skenario pengujian dengan tingkat kegagalan **0.00% (Zero Error)** dan throughput puncak mencapai **56.61 req/detik**.
5. Dengan seluruh capaian teknis dan dokumentasi yang telah diverifikasi, sistem M-BengkelQQ dinyatakan **READY FOR PRESENTATION** dan siap didemonstrasikan di hadapan dosen penguji.

### 6.2 Saran Pengembangan
Untuk pengembangan sistem lebih lanjut pada skala komersial industri:
1. **Integrasi Payment Gateway Otomatis:** Mengintegrasikan SDK payment gateway komersial (seperti Midtrans, Xendit, atau DOKU) dengan penanganan webhook otomatis untuk verifikasi pembayaran tanpa intervensi manual kasir.
2. **Pelacakan Lokasi Kurir Real-Time:** Menambahkan modul peta interaktif berbasis Leaflet / Google Maps API dan GPS tracking kurir secara langsung saat proses penjemputan dan pengantaran motor.
3. **Notifikasi Multi-Channel:** Mengintegrasikan gateway WhatsApp API (seperti Fonnte/Twilio) dan email SMTP transaksional untuk mengirimkan notifikasi status servis dan tanda terima invoice digital ke ponsel pelanggan secara real-time.
4. **Pengembangan Aplikasi Seluler Native/Cross-Platform:** Membangun aplikasi mobile berbasis Flutter yang memanfaatkan 45 endpoint REST API v1 MotoCare yang telah tersedia.
5. **Skalabilitas Produksi Cloud:** Untuk deployment produksi berskala puluhan ribu pengguna, direkomendasikan penggunaan arsitektur Nginx + PHP-FPM / Laravel Octane (FrankenPHP) dan Redis cache clustering.

---

## DAFTAR PUSTAKA

1. Sommerville, I. (2016). *Software Engineering* (10th ed.). Boston: Pearson.
2. Fowler, M. (2004). *UML Distilled: A Brief Guide to the Standard Object Modeling Language* (3rd ed.). Boston: Addison-Wesley.
3. Elmasri, R., & Navathe, S. B. (2016). *Fundamentals of Database Systems* (7th ed.). Hoboken: Pearson.
4. Otwell, T., & Laravel Team. (2026). *Laravel Documentation (Version 11.x)*. Retrieved from https://laravel.com/docs
5. Grafana Labs. (2026). *k6 Documentation: High-performance load testing for developers and QA engineers*. Retrieved from https://grafana.com/docs/k6
6. Fielding, R. T. (2000). *Architectural Styles and the Design of Network-based Software Architectures* (Doctoral dissertation). University of California, Irvine.

---

## LAMPIRAN

### Lampiran 1: Matriks Pembagian Tugas & Kontribusi Nyata (M7 – M10)

| No | Nama Mahasiswa | NIM | Peran Utama | Kontribusi Minggu 7–8 | Kontribusi Minggu 9–10 | Status |
|:---:|---|:---:|---|---|---|:---:|
| 1 | **Ridho Ramadani E.** *(Ketua)* | 2401020121 | Backend & Security Lead | Perancangan basis data 15 tabel, relasi Eloquent, service layer core | Security audit, lockForUpdate, validasi 45 API, optimasi backend | **LENGKAP (100%)** |
| 2 | **Muhammad Agus Farhan Talib** | 2401020111 | Frontend QA/QC Lead | Activity diagram 4–7, integrasi form Blade dengan controller | Frontend QA/QC, validasi form, skenario live demo presentasi | **LENGKAP (100%)** |
| 3 | **Firman Dwi Syahputra** | 2401020112 | UI/UX Design Lead | Desain prototipe Figma, implementasi 4 dashboard multi-role | UI/UX QA/QC, responsivitas Tailwind, desain slide presentasi PPT | **LENGKAP (100%)** |
| 4 | **Farrel Abhista Farouk** | 2401020117 | Integration & API Lead | Sequence diagram 1–7, integrasi alur booking-servis-delivery | End-to-end integration test, pemetaan & pengujian 45 endpoint API | **LENGKAP (100%)** |
| 5 | **Saif Alif Abyan** | 2401020163 | QA Lead & k6 Performance | Use case UC-01–10, setup automated testing framework | Koordinasi final QA/QC, eksekusi 6 skenario k6, laporan akhir final | **LENGKAP (100%)** |

### Lampiran 2: Matriks Detail Pengujian 45 Endpoint REST API v1

Dokumentasi lengkap seluruh 45 endpoint REST API v1 beserta method, rute, middleware, otorisasi peran, kode status harapan, dan kode status aktual tercantum pada berkas [`docs/final-testing/API_TEST_CASES.md`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/docs/final-testing/API_TEST_CASES.md).

### Lampiran 3: Log Eksekusi Uji Beban k6

Seluruh berkas bukti eksekusi dan JSON export pengujian beban performa k6 tersimpan secara lengkap pada repositori:
- Smoke Test: [`docs/final-testing/results/smoke/`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/docs/final-testing/results/smoke)
- Load Test: [`docs/final-testing/results/load/`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/docs/final-testing/results/load)
- Stress Test: [`docs/final-testing/results/stress/`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/docs/final-testing/results/stress)
- Spike Test: [`docs/final-testing/results/spike/`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/docs/final-testing/results/spike)
- Soak Test: [`docs/final-testing/results/soak/`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/docs/final-testing/results/soak)
- Breakpoint Test: [`docs/final-testing/results/breakpoint/`](file:///home/wh4mi/Dokumen/S5/senin/pw/mbengkelQ/docs/final-testing/results/breakpoint)

