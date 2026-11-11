# PANDUAN SLIDE PRESENTASI FINAL PROYEK — MOTOCARE (MB-ENGKELQQ)
## Presentasi Akhir Mata Kuliah Pemrograman Web

---

## DAFTAR ALOKASI PEMBICARA (SPEAKER ALLOCATION)

- **Slide 1 – 3:** Pembukaan, Latar Belakang, & Use Case (**Saif Alif Abyan**)
- **Slide 4 – 6:** Arsitektur Sistem, Database, & Keamanan Backend (**Ridho Ramadani E.**)
- **Slide 7 – 9:** Alur Integrasi Bisnis & Cakupan 45 Endpoint REST API (**Farrel Abhista Farouk**)
- **Slide 10 – 11:** Antarmuka Multi-Role UI/UX & Desain Responsif (**Firman Dwi Syahputra**)
- **Slide 12 – 13:** Pengujian Integrasi Frontend & Skenario Live Demo (**Muhammad Agus Farhan Talib**)
- **Slide 14 – 16:** Hasil Pengujian Beban Grafana k6, Rekomendasi, & Penutup (**Saif Alif Abyan**)

---

## STRUKTUR SLIDE LENGKAP & NASKAH PRESENTASI

### SLIDE 1: JUDUL & IDENTITAS PROYEK
* **Judul Slide:** MotoCare (MB-engkelQQ) — Sistem Informasi Manajemen Perbengkelan Terpadu
* **Sub-Judul:** Final Project Presentation — Pemrograman Web (Semester 5 / Senin)
* **Konten Visual:**
  - Logo MotoCare / Ilustrasi Modern Bengkel Digital
  - Identitas Tim:
    1. Ridho Ramadani E. (2401020121)
    2. Muhammad Agus Farhan Talib (2401020111)
    3. Firman Dwi Syahputra (2401020112)
    4. Farrel Abhista Farouk (2401020117)
    5. Saif Alif Abyan (2401020163)
* **Speaker:** Saif Alif Abyan
* **Speaker Notes:**
  > *"Selamat pagi/siang Bapak/Ibu Dosen penguji dan rekan-rekan sekalian. Hari ini kami dari tim MotoCare akan mempresentasikan hasil akhir sistem informasi perbengkelan terintegrasi MB-engkelQQ yang telah kami rancang, kembangkan, dan uji secara komprehensif dari Minggu 7 hingga tahap final testing Minggu 10."*

---

### SLIDE 2: LATAR BELAKANG & PERMASALAHAN MITRA
* **Judul Slide:** Permasalahan Operasional Bengkel Konvensional
* **Poin Utama:**
  1. **Pencatatan Manual:** Rentan manipulasi nota, stok suku cadang sering tidak sinkron dengan fisik.
  2. **Workload Imbalance:** Penugasan teknisi tidak merata, sering menumpuk pada satu mekanik tertentu.
  3. **Keterbatasan Layanan Antar-Jemput:** Pelanggan sibuk kesulitan servis karena bengkel tidak memiliki tracking penjemputan terpadu.
  4. **Verifikasi Pembayaran Manual:** Rawan bukti transfer fiktif atau konfirmasi ganda tanpa audit trail.
* **Speaker:** Saif Alif Abyan
* **Speaker Notes:**
  > *"Sistem bengkel konvensional sering menghadapi empat kendala utama: nota manual yang rawan manipulasi harga, antrean mekanik yang tidak seimbang, ketidakmampuan melacak antar-jemput motor secara real-time, dan risiko pembayaran palsu. MotoCare hadir mengatasi seluruh masalah ini melalui digitalisasi menyeluruh."*

---

### SLIDE 3: SOLUSI MOTOCARE & 4 AKTOR UTAMA
* **Judul Slide:** Solusi Terpadu Berbasis 4 Role (RBAC)
* **Poin Utama:**
  - **Customer:** Registrasi kendaraan, booking reguler / pickup, pantau servis real-time, invoice transparan.
  - **Mekanik:** Menerima tugas otomatis sesuai spesialisasi mesin (4-Tak / Matic), input diagnosis, potong stok suku cadang.
  - **Kurir:** Penjemputan motor dari customer ke bengkel, dan pengantaran motor selesai servis.
  - **Admin:** Konfirmasi pesanan, auto-assignment mekanik, verifikasi transfer/QRIS, kelola master tarif dan staf.
* **Speaker:** Saif Alif Abyan
* **Speaker Notes:**
  > *"MotoCare merancang arsitektur Role-Based Access Control dengan 4 persona yang saling terhubung erat: Pelanggan, Mekanik, Kurir, dan Admin bengkel. Setiap pengguna memiliki hak akses dan antarmuka dashboard yang terisolasi."*

---

### SLIDE 4: ARSITEKTUR BACKEND & STACK TEKNOLOGI
* **Judul Slide:** Arsitektur Sistem & Stack Teknologi
* **Poin Utama:**
  - **Backend Framework:** Laravel 11.56.1 (PHP 8.4)
  - **Frontend Layer:** Blade Templating Engine + Tailwind CSS (Responsive Utility-First)
  - **Database Engine:** MariaDB / SQLite (15 Migrations, Foreign Key Cascades, Indexing)
  - **API Layer:** RESTful API v1 terproteksi Laravel Sanctum (Token-Based Authentication)
  - **Security:** CSRF Protection, Mass Assignment Protection, Rate Limiting (`throttle:5,1`)
* **Speaker:** Ridho Ramadani E.
* **Speaker Notes:**
  > *"Kami membangun MotoCare menggunakan Laravel 11 dengan arsitektur bersih. Kami memisahkan controller antara web interface dan RESTful API v1. Seluruh rute API diproteksi dengan Sanctum bearer tokens, dan endpoint publik diamankan dengan rate limiting ketat."*

---

### SLIDE 5: ALGORITMA AUTO-ASSIGNMENT & PRICE SNAPSHOT
* **Judul Slide:** Rekayasa Fitur Unggulan Sistem
* **Poin Utama:**
  - **Auto-Assignment Service:**
    - Mengevaluasi tipe mesin motor customer (`4_tak`, `matic`, dll).
    - Memilih mekanik aktif dengan spesialisasi yang cocok dan beban tugas aktif paling sedikit (*lowest queue*).
  - **Price Snapshot Protection:**
    - Harga jasa dan sparepart dikunci pada detik transaksi dibuat (`service_order_items.price`).
    - Kebal terhadap perubahan harga master di masa depan.
  - **Pessimistic Row Locking (`lockForUpdate`):**
    - Mencegah double-spending atau race condition saat admin menyetujui mutasi pembayaran.
* **Speaker:** Ridho Ramadani E.
* **Speaker Notes:**
  > *"Dua keunggulan utama rekayasa perangkat lunak kami adalah: Pertama, algoritma auto-assignment yang menyeimbangkan beban kerja teknisi berdasarkan spesialisasi mesin. Kedua, mekanisme price snapshot yang menjamin tagihan invoice tidak akan pernah terpengaruh jika admin menaikkan harga master sparepart di kemudian hari."*

---

### SLIDE 6: SKEMA BASIS DATA & KEAMANAN
* **Judul Slide:** Struktur Basis Data & Integritas Relasi
* **Poin Utama:**
  - 15 Tabel Relasional: `users`, `vehicles`, `bookings`, `service_orders`, `service_order_items`, `service_items`, `payments`, `delivery_tasks`, `audit_logs`, dll.
  - Integritas Referensial: Foreign Key Constraints dengan strategi restrict/cascade terencana.
  - Logging Audit Mutasi: Setiap perubahan status transaksi dan stok sparepart tercatat di tabel `audit_logs`.
* **Speaker:** Ridho Ramadani E.
* **Speaker Notes:**
  > *"Basis data dirancang dengan normalisasi tingkat ketiga. Kami melacak setiap mutasi status booking, pembayaran, dan stok barang melalui tabel audit logs, sehingga seluruh aktivitas staf bengkel memiliki jejak audit yang dapat dipertanggungjawabkan."*

---

### SLIDE 7: ALUR BISNIS 1 — REGULER SERVICE WORKFLOW
* **Judul Slide:** Alur Bisnis Servis Reguler (End-to-End)
* **Poin Utama (Diagram Alir):**
  1. Customer: Buat Booking Servis di Web.
  2. Admin: Konfirmasi Booking -> Sistem otomatis menugaskan Mekanik.
  3. Mekanik: Mulai Servis -> Simpan Diagnosis Kerusakan -> Tambah Sparepart (Stok terpotong).
  4. Mekanik: Selesaikan Servis -> Invoice resmi terbit.
  5. Customer: Bayar via Transfer Bank / QRIS -> Unggah bukti bayar.
  6. Admin: Verifikasi Bukti -> Status Booking Berubah Selesai.
* **Speaker:** Farrel Abhista Farouk
* **Speaker Notes:**
  > *"Pada alur servis reguler, sistem mengotomatisasi transisi dari pesanan baru hingga invoice selesai. Validasi sistem mewajibkan mekanik mengisi catatan diagnosis kerusakan sebelum dapat menyelesaikan pesanan servis."*

---

### SLIDE 8: ALUR BISNIS 2 — LAYANAN PICKUP & DELIVERY
* **Judul Slide:** Alur Bisnis Layanan Antar-Jemput
* **Poin Utama:**
  - Customer memilih opsi penjemputan -> Sistem menghitung estimasi ongkir per kilometer.
  - Admin menugaskan Kurir -> Kurir menjemput kendaraan (*in-transit*).
  - Kurir menyelesaikan penjemputan di bengkel -> Service Order otomatis dibuat dan mekanik langsung di-assign.
  - Setelah servis dan pembayaran selesai diverifikasi -> Kurir mengantar kembali kendaraan ke rumah customer.
* **Speaker:** Farrel Abhista Farouk
* **Speaker Notes:**
  > *"Inovasi layanan antar-jemput MotoCare terintegrasi penuh. Begitu kurir mengonfirmasi bahwa motor telah tiba di bengkel, sistem secara cerdas langsung meng-create service order dan menugaskan teknisi tanpa perlu intervensi manual tambahan dari admin."*

---

### SLIDE 9: CAKUPAN PENGUJIAN 45 ENDPOINT REST API
* **Judul Slide:** Kepatuhan & Verifikasi 45 REST API v1
* **Poin Utama:**
  - **Total Endpoint:** 45 Endpoints REST API v1.
  - **Tingkat Kelulusan:** 100% PASS (45/45).
  - **Cakupan Modul API:** Auth (4), Vehicles (5), Bookings (5), Service Orders (6), Service Items (6), Payments (4), Delivery Tasks (5), Settings (4), Invoices (2), Notifications (3), Dashboard (1).
  - Seluruh respons mematuhi format JSON standar dengan kode status HTTP semantik.
* **Speaker:** Farrel Abhista Farouk
* **Speaker Notes:**
  > *"Kami mengaudit dan memverifikasi seluruh 45 route API yang terdaftar di sistem. Seluruh endpoint terproteksi otorisasi Sanctum, tervalidasi dengan 142 feature assertions, dan siap diintegrasikan dengan aplikasi mobile atau klien pihak ketiga."*

---

### SLIDE 10: USER INTERFACE (UI/UX) MULTI-DASHBOARD
* **Judul Slide:** Desain Antarmuka Multi-Role & Responsivitas
* **Poin Utama:**
  - **Dashboard Customer:** Tampilan ringkas status motor, histori booking, dan tombol bayar cepat.
  - **Dashboard Mekanik:** Daftar antrean servis aktif, formulir input diagnosis, dan pencarian suku cadang.
  - **Dashboard Kurir:** Antarmuka mobile-friendly untuk daftar tugas pickup dan pengantaran alamat.
  - **Dashboard Admin:** Statistik harian antrean bengkel, verifikasi bukti pembayaran, dan stok barang.
* **Speaker:** Firman Dwi Syahputra
* **Speaker Notes:**
  > *"Dari sisi UI/UX, kami mengedepankan efisiensi visual. Dashboard admin memfokuskan pada metriks antrean dan verifikasi cepat, sementara dashboard mekanik dan kurir dioptimalkan dengan tombol aksi berukuran ergonomis agar mudah dioperasikan di lantai bengkel maupun di jalan."*

---

### SLIDE 11: DESAIN RESPONSIF & KONSISTENSI VISUAL
* **Judul Slide:** Responsivitas & Komponen Desain
* **Poin Utama:**
  - Dibangun dengan framework utilitas Tailwind CSS.
  - Tampilan adaptif penuh: Smartphone (Mobile View), Tablet, dan Monitor Desktop.
  - Komponen modular: Flash Session Alert, Modal Verifikasi Pembayaran, Badge Status Semantik (Kuning = Pending, Biru = In Progress, Hijau = Completed, Merah = Cancelled/Rejected).
* **Speaker:** Firman Dwi Syahputra
* **Speaker Notes:**
  > *"Seluruh tampilan telah diuji pada resolusi desktop maupun layar seluler tanpa ada elemen yang terpotong. Status transaksi divisualisasikan dengan palet warna semantik standar untuk meminimalkan beban kognitif pengguna."*

---

### SLIDE 12: INTEGRASI FRONTEND & VALIDASI FORM
* **Judul Slide:** Pengujian Integrasi Frontend-Backend
* **Poin Utama:**
  - Validasi formulir server-side yang aman dengan pesan error berbahasa Indonesia yang jelas.
  - Pencegahan double-submit pada tombol pembayaran dan booking.
  - Feedback interaktif: Flash notification saat sukses menambah kendaraan, membatalkan pesanan, atau upload transfer.
* **Speaker:** Muhammad Agus Farhan Talib
* **Speaker Notes:**
  > *"Kami memastikan setiap interaksi form web terhubung aman ke backend. Form validasi mencegah input tanggal lampau pada booking, memvalidasi ukuran gambar bukti transfer maksimal 2MB, dan mengarahkan pengguna kembali dengan notifikasi flash yang intuitif."*

---

### SLIDE 13: SKENARIO DEMO LANGSUNG (LIVE DEMO GUIDE)
* **Judul Slide:** Skenario Demonstrasi Sistem (Live Demo)
* **Poin Utama:**
  1. **Demo 1 (Customer):** Login -> Tambah Motor Honda Vario -> Booking Servis Ringan.
  2. **Demo 2 (Admin):** Konfirmasi Booking -> Bukti Auto-Assignment Mekanik Budi terpilih.
  3. **Demo 3 (Mekanik):** Mulai Servis -> Input Diagnosis -> Tambah Oli MPX2 (Stok berkurang dari 50 ke 49) -> Selesaikan Servis.
  4. **Demo 4 (Customer & Admin):** Customer lihat invoice -> Upload bukti bayar -> Admin setujui pembayaran -> Transaksi selesai penuh.
* **Speaker:** Muhammad Agus Farhan Talib
* **Speaker Notes:**
  > *"Dalam sesi live demo ini, kami akan memperlihatkan perjalanan alur transaksi nyata dari awal pemesanan oleh customer hingga invoice lunas disetujui admin, menunjukkan bagaimana stok suku cadang terpotong secara otomatis dan status transaksi bertransisi secara konsisten."*

---

### SLIDE 14: HASIL PENGUJIAN REGRESI (340 TESTS PASS)
* **Judul Slide:** Verifikasi Pengujian Otomatis (PHPUnit)
* **Poin Utama:**
  - **340 Automated Feature & Unit Tests** dijalankan via PHPUnit.
  - **801 Assertions** berhasil diuji.
  - **Waktu Eksekusi:** 31.02 detik.
  - **Tingkat Kelulusan:** 100% Lulus (0 Gagal, 0 Error).
  - Cakupan menyeluruh: Otentikasi, Hak Akses RBAC, Price Snapshot, Anti-Price Tampering, Transisi Status, dan Isolasi Data Multi-Tenant.
* **Speaker:** Saif Alif Abyan
* **Speaker Notes:**
  > *"Kualitas kode MotoCare dijamin dengan 340 skenario pengujian otomatis. Kami menguji kasus positif dan skenario penyerangan negatif, seperti percobaan customer lain melihat kendaraan orang lain atau mekanik mencoba mengubah harga suku cadang, dan seluruhnya terbukti berhasil dicegah oleh sistem."*

---

### SLIDE 15: HASIL PENGUJIAN BEBAN GRAFANA K6
* **Judul Slide:** Uji Ketahanan & Performa Beban Tinggi (k6)
* **Poin Utama (Grafik & Tabel Metrik):**
  - **Smoke Test (2 VU):** 140 req, error 0.00%, p95 82.26 ms -> **PASS**
  - **Load Test (10 VU):** 1,070 req, error 0.00%, p95 306.04 ms -> **PASS**
  - **Stress Test (25 VU):** 2,244 req, error 0.00%, p95 525.93 ms -> **PASS**
  - **Spike Test (25 VU surge):** 1,458 req, error 0.00%, p95 305.13 ms -> **PASS**
  - **Soak Test (6 VU, 70 detik):** 1,623 req, error 0.00%, p95 104.84 ms -> **PASS**
  - **Breakpoint Test (45 VU):** 3,423 req, error 0.00%, p95 505.56 ms, throughput 56.61 req/s -> **PASS**
  - **Akumulasi Total:** **9,958 requests teruji dengan 0% failure rate.**
* **Speaker:** Saif Alif Abyan
* **Speaker Notes:**
  > *"Sesuai instruksi khusus dosen pada Minggu 10, kami menjalankan pengujian performa mendalam dengan Grafana k6 melalui 6 skenario uji. Hasil aktual membuktikan sistem melayani hampir sepuluh ribu permintaan HTTP tanpa satu pun kegagalan, membuktikan stabilitas dan kesiapan konkurensi arsitektur kami."*

---

### SLIDE 16: KESIMPULAN & STATUS KESIAPAN AKHIR
* **Judul Slide:** Kesimpulan & Kesiapan Presentasi
* **Poin Utama:**
  - Sistem MotoCare telah diselesaikan **100% Final**.
  - Seluruh modul fungsional terintegrasi dan siap digunakan.
  - Tingkat kelulusan tes fungsional dan beban performa mencapai 100%.
  - Dokumentasi teknis dan laporan kontribusi tim telah disusun secara lengkap.
* **STATUS AKHIR:** **READY FOR PRESENTATION**
* **Speaker:** Saif Alif Abyan
* **Speaker Notes:**
  > *"Sebagai penutup, sistem MotoCare berada dalam kondisi 100% final, terverifikasi, dan siap dipresentasikan. Terima kasih atas perhatian Bapak/Ibu dosen penguji, kami membuka sesi tanya jawab."*

---

## ANTISIPASI PERTANYAAN DOSEN & JAWABAN REKOMENDASI

1. **Pertanyaan: Bagaimana sistem mencegah manipulasi harga suku cadang jika ada request jahat dari browser atau inspect element?**
   - *Jawaban Rekomendasi (Ridho / Farrel):* "Sistem kami mengabaikan field harga atau subtotal yang dikirim dari request klien. Pada controller `ServiceOrderController::addItem`, harga diambil murni dari tabel master `service_items` di server, lalu nilainya di-snapshot ke tabel relasi transaksi. Hal ini dibuktikan melalui pengujian `PriceSnapshotTest::test_4_manipulasi_price_snapshot_diabaikan` yang lulus 100%."

2. **Pertanyaan: Mengapa pengujian login tidak diuji hingga ratusan request per detik pada k6?**
   - *Jawaban Rekomendasi (Saif / Ridho):* "Karena endpoint `/api/v1/auth/login` telah kami pasang pengaman `throttle:5,1` untuk mencegah serangan brute force password. Jika di-spam ratusan request, sistem secara sengaja merespons 429 Too Many Requests sebagai fitur keamanan. Oleh karena itu, pengujian beban performa transaksi kami jalankan menggunakan bearer token Sanctum yang diinjeksi via environment variables."

3. **Pertanyaan: Bagaimana algoritma auto-assignment memastikan keadilan pembagian kerja antar mekanik?**
   - *Jawaban Rekomendasi (Farrel / Ridho):* "Service kami melakukan query terhadap mekanik aktif yang memiliki spesialisasi sesuai tipe mesin motor, kemudian menghitung jumlah `service_orders` yang berstatus aktif (`pending` atau `in_progress`). Mekanik dengan hitungan tugas terendah akan dipilih secara otomatis."

