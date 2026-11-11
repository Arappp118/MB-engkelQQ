import sys
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.enum.text import PP_ALIGN
from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE

def create_deck():
    prs = Presentation()
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6]

    # Color Palette (Modern Dark Slate & Cyan Theme)
    BG_DARK = RGBColor(15, 23, 42)      # Slate 900
    CARD_BG = RGBColor(30, 41, 59)      # Slate 800
    ACCENT_CYAN = RGBColor(14, 165, 233)# Sky 500
    ACCENT_GREEN = RGBColor(16, 185, 129)# Emerald 500
    TEXT_WHITE = RGBColor(248, 250, 252)# Slate 50
    TEXT_MUTED = RGBColor(148, 163, 184)# Slate 400
    CARD_BORDER = RGBColor(51, 65, 85)  # Slate 700

    def add_base_slide(title, category="MOTOCARE (MB-ENGKELQQ) • FINAL PRESENTATION"):
        slide = prs.slides.add_slide(blank_layout)
        # Background
        bg = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(7.5))
        bg.fill.solid()
        bg.fill.fore_color.rgb = BG_DARK
        bg.line.fill.background()

        # Category / Header badge
        cat_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.5), Inches(11.7), Inches(0.4))
        tf_cat = cat_box.text_frame
        tf_cat.word_wrap = True
        p_cat = tf_cat.paragraphs[0]
        p_cat.text = category.upper()
        p_cat.font.size = Pt(11)
        p_cat.font.bold = True
        p_cat.font.color.rgb = ACCENT_CYAN

        # Title
        title_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.85), Inches(11.7), Inches(0.8))
        tf_title = title_box.text_frame
        tf_title.word_wrap = True
        p_title = tf_title.paragraphs[0]
        p_title.text = title
        p_title.font.size = Pt(24)
        p_title.font.bold = True
        p_title.font.color.rgb = TEXT_WHITE

        return slide

    def add_card(slide, left, top, width, height, title, items, badge=""):
        card = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(left), Inches(top), Inches(width), Inches(height))
        card.fill.solid()
        card.fill.fore_color.rgb = CARD_BG
        card.line.color.rgb = CARD_BORDER
        card.line.width = Pt(1)

        tx_box = slide.shapes.add_textbox(Inches(left + 0.25), Inches(top + 0.2), Inches(width - 0.5), Inches(height - 0.4))
        tf = tx_box.text_frame
        tf.word_wrap = True

        p_t = tf.paragraphs[0]
        p_t.text = title
        p_t.font.size = Pt(16)
        p_t.font.bold = True
        p_t.font.color.rgb = ACCENT_CYAN
        p_t.space_after = Pt(10)

        for item in items:
            p = tf.add_paragraph()
            p.text = "• " + item
            p.font.size = Pt(12)
            p.font.color.rgb = TEXT_WHITE
            p.space_after = Pt(6)

    # ── SLIDE 1: COVER ──
    s1 = prs.slides.add_slide(blank_layout)
    bg1 = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(7.5))
    bg1.fill.solid()
    bg1.fill.fore_color.rgb = BG_DARK
    bg1.line.fill.background()

    tb1 = s1.shapes.add_textbox(Inches(1.0), Inches(1.5), Inches(11.3), Inches(4.5))
    tf1 = tb1.text_frame
    tf1.word_wrap = True

    p_badge = tf1.paragraphs[0]
    p_badge.text = "PEMROGRAMAN WEB (SEMESTER 5 / SENIN) • FINAL PROJECT"
    p_badge.font.size = Pt(13)
    p_badge.font.bold = True
    p_badge.font.color.rgb = ACCENT_CYAN
    p_badge.space_after = Pt(12)

    p_main = tf1.add_paragraph()
    p_main.text = "MotoCare (MB-engkelQQ)"
    p_main.font.size = Pt(40)
    p_main.font.bold = True
    p_main.font.color.rgb = TEXT_WHITE
    p_main.space_after = Pt(8)

    p_sub = tf1.add_paragraph()
    p_sub.text = "Sistem Informasi Manajemen Bengkel Terintegrasi Berbasis Multi-Role & RESTful API v1"
    p_sub.font.size = Pt(18)
    p_sub.font.color.rgb = TEXT_MUTED
    p_sub.space_after = Pt(28)

    p_team = tf1.add_paragraph()
    p_team.text = "Tim Pengembang:\n1. Ridho Ramadani E. (2401020121)  •  2. M. Agus Farhan Talib (2401020111)  •  3. Firman Dwi Syahputra (2401020112)\n4. Farrel Abhista Farouk (2401020117)  •  5. Saif Alif Abyan (2401020163)"
    p_team.font.size = Pt(13)
    p_team.font.color.rgb = ACCENT_GREEN

    # ── SLIDE 2: PERMASALAHAN MITRA ──
    s2 = add_base_slide("Permasalahan Operasional Bengkel Konvensional")
    add_card(s2, 0.8, 1.8, 5.6, 5.0, "Kelemahan Sistem Konvensional", [
        "Pencatatan nota servis manual yang rentan rusak dan hilang.",
        "Ketidakseimbangan beban kerja mekanik (workload imbalance).",
        "Ketiadaan fasilitas antar-jemput motor secara terintegrasi.",
        "Rawan manipulasi harga suku cadang dan pemalsuan transfer.",
        "Pelanggan tidak dapat memantau status pengerjaan secara real-time."
    ])
    add_card(s2, 6.8, 1.8, 5.6, 5.0, "Dampak Negatif Operasional", [
        "Antrean teknisi menumpuk pada satu orang tertentu.",
        "Pendapatan bengkel bocor akibat perbedaan harga master dan nota.",
        "Hilangnya potensi pelanggan sibuk yang butuh layanan pickup.",
        "Penyelesaian pesanan lambat tanpa tracking diagnosis yang jelas.",
        "Ketiadaan laporan audit finansial dan mutasi stok suku cadang."
    ])

    # ── SLIDE 3: SOLUSI MOTOCARE (4 AKTOR RBAC) ──
    s3 = add_base_slide("Solusi Terpadu Berbasis 4 Role Pengguna (RBAC)")
    add_card(s3, 0.8, 1.8, 2.7, 5.0, "Customer", [
        "Registrasi kendaraan.",
        "Booking servis reguler.",
        "Opsi antar-jemput.",
        "Tracking status live.",
        "Invoice & bukti bayar."
    ])
    add_card(s3, 3.8, 1.8, 2.7, 5.0, "Mekanik", [
        "Auto-assigned tugas.",
        "Input diagnosis motor.",
        "Tambah suku cadang.",
        "Potong stok otomatis.",
        "Selesaikan pengerjaan."
    ])
    add_card(s3, 6.8, 1.8, 2.7, 5.0, "Kurir", [
        "Terima tugas jemput.",
        "Update in-transit.",
        "Antar motor ke bengkel.",
        "Antar balik ke customer.",
        "Tracking alamat pickup."
    ])
    add_card(s3, 9.8, 1.8, 2.7, 5.0, "Admin", [
        "Konfirmasi booking.",
        "Verifikasi bayar transfer.",
        "Kelola harga & stok.",
        "Manajemen mekanik/staf.",
        "Monitoring antrean."
    ])

    # ── SLIDE 4: ARSITEKTUR BACKEND & STACK TEKNOLOGI ──
    s4 = add_base_slide("Arsitektur Sistem & Stack Teknologi")
    add_card(s4, 0.8, 1.8, 3.7, 5.0, "Backend & Database", [
        "Framework: Laravel 11.56.1 (PHP 8.4)",
        "Database: MariaDB / SQLite terisolasi",
        "15 Skema Migrasi Ternormalisasi",
        "Pessimistic Row Locking pada transaksi",
        "Eloquent ORM dengan strict cascading"
    ])
    add_card(s4, 4.8, 1.8, 3.7, 5.0, "Frontend & Desain", [
        "Blade Server-Side Rendering",
        "Tailwind CSS Utility-First Framework",
        "Tampilan responsif Mobile & Desktop",
        "Flash Session & Form Feedback",
        "Badge status semantik visual"
    ])
    add_card(s4, 8.8, 1.8, 3.7, 5.0, "API & Security Layer", [
        "RESTful API v1 terproteksi Sanctum",
        "Middleware Throttle 5 req/min (Login)",
        "CSRF & Mass Assignment Protection",
        "Anti-Price Tampering Calculation",
        "Role-Based Access Guards"
    ])

    # ── SLIDE 5: FITUR UNGGULAN REKAYASA ──
    s5 = add_base_slide("Rekayasa Fitur Unggulan Sistem")
    add_card(s5, 0.8, 1.8, 3.7, 5.0, "Auto-Assignment Mekanik", [
        "Evaluasi spesialisasi mesin motor (4-Tak / Matic).",
        "Memilih mekanik aktif dengan antrean tugas paling sedikit.",
        "Otomatis aktif saat booking dikonfirmasi atau pickup selesai.",
        "Idempoten: Mencegah duplikasi service order."
    ])
    add_card(s5, 4.8, 1.8, 3.7, 5.0, "Price Snapshot Protection", [
        "Harga suku cadang dikunci saat order item dibuat.",
        "Perubahan harga master masa depan tidak mengubah transaksi lama.",
        "Subtotal & Grand Total dihitung ulang di server.",
        "Stok suku cadang berkurang secara atomik."
    ])
    add_card(s5, 8.8, 1.8, 3.7, 5.0, "Pessimistic Locking & Audit", [
        "lockForUpdate() pada transaksi verifikasi bayar.",
        "Mencegah double verification dan race condition.",
        "paid_at hanya terisi saat admin approve mutasi.",
        "Tabel audit_logs mencatat mutasi status penting."
    ])

    # ── SLIDE 6: ALUR BISNIS 1 — REGULER ──
    s6 = add_base_slide("Alur Bisnis Servis Reguler (End-to-End)")
    add_card(s6, 0.8, 1.8, 5.6, 5.0, "Tahap 1: Pemesanan & Pengerjaan", [
        "1. Customer login dan membuat booking servis motor.",
        "2. Admin konfirmasi booking di dashboard admin.",
        "3. Sistem otomatis menugaskan mekanik terspesialisasi.",
        "4. Mekanik memulai servis & wajib input diagnosis.",
        "5. Mekanik menambahkan suku cadang (stok otomatis terpotong)."
    ])
    add_card(s6, 6.8, 1.8, 5.6, 5.0, "Tahap 2: Tagihan & Pembayaran", [
        "6. Mekanik menyelesaikan servis -> Invoice resmi terbit.",
        "7. Customer menerima rincian invoice transparan.",
        "8. Customer bayar transfer bank / QRIS dan upload bukti.",
        "9. Admin memverifikasi keabsahan bukti bayar.",
        "10. Transaksi selesai lunas, notifikasi dikirim ke customer."
    ])

    # ── SLIDE 7: ALUR BISNIS 2 — PICKUP & DELIVERY ──
    s7 = add_base_slide("Alur Bisnis Layanan Antar-Jemput (Pickup & Delivery)")
    add_card(s7, 0.8, 1.8, 5.6, 5.0, "Tahap Penjemputan (Pickup)", [
        "1. Customer booking dengan mencentang opsi penjemputan.",
        "2. Sistem menghitung ongkir per km secara transparan.",
        "3. Admin menugaskan kurir untuk menjemput motor.",
        "4. Kurir update status penjemputan menjadi in-transit.",
        "5. Motor tiba di bengkel -> Service Order langsung terbuat otomatis."
    ])
    add_card(s7, 6.8, 1.8, 5.6, 5.0, "Tahap Pengantaran (Delivery)", [
        "6. Mekanik menyelesaikan diagnosis & pengerjaan servis.",
        "7. Customer melunasi biaya servis + ongkir antar-jemput.",
        "8. Admin verifikasi pembayaran dan aktifkan tugas delivery.",
        "9. Kurir mengantarkan motor kembali ke alamat customer.",
        "10. Kurir konfirmasi pengerjaan -> Transaksi tuntas 100%."
    ])

    # ── SLIDE 8: CAKUPAN 45 ENDPOINT REST API ──
    s8 = add_base_slide("Kepatuhan & Pengujian 45 Endpoint REST API v1")
    add_card(s8, 0.8, 1.8, 5.6, 5.0, "Metrik Endpoint Teruji", [
        "Total Endpoint Terdaftar: 45 Endpoints",
        "Endpoint Lulus (PASS): 45 (100.00%)",
        "Endpoint Gagal (FAIL): 0 (0.00%)",
        "Endpoint Publik: /api/v1/health & /api/v1/auth/login",
        "Endpoint Terproteksi: 43 Endpoints via Sanctum Token"
    ])
    add_card(s8, 6.8, 1.8, 5.6, 5.0, "Distribusi Modul API", [
        "Auth: 4 routes (login, logout, me, refresh)",
        "Kendaraan & Booking: 10 routes (CRUD + Cancel/Confirm)",
        "Service Order & Items: 12 routes (Diagnosis, Parts, Stok)",
        "Pembayaran & Invoice: 6 routes (Pay, Verify, Reject, Invoice)",
        "Delivery & Pengaturan: 9 routes (Tasks, Settings)",
        "Dashboard & Notifikasi: 4 routes adaptif per role"
    ])

    # ── SLIDE 9: VERIFIKASI PENGUJIAN OTOMATIS (342 TESTS) ──
    s9 = add_base_slide("Verifikasi Kualitas: 342 Automated Feature Tests")
    add_card(s9, 0.8, 1.8, 5.6, 5.0, "Ringkasan Eksekusi PHPUnit", [
        "Total Test Suites: 22 Test Classes",
        "Total Tests Dijalankan: 342 Feature & Unit Tests",
        "Total Assertions: 819 Assertions",
        "Durasi Eksekusi: 38.43 detik",
        "Tingkat Kelulusan: 100% PASS (0 Failed, 0 Error)"
    ])
    add_card(s9, 6.8, 1.8, 5.6, 5.0, "Cakupan Pengujian Kritis", [
        "Keamanan: Percobaan price tampering ditolak server.",
        "Siklus Kendaraan: Tambah, edit, hapus, & tambah lagi 100% pass.",
        "Otorisasi: Customer lain ditolak akses data orang lain (403).",
        "Validasi: Penambahan item pada order selesai ditolak.",
        "Stok: Penambahan sparepart otomatis memotong kuantitas."
    ])

    # ── SLIDE 10: HASIL PENGUJIAN BEBAN GRAFANA K6 ──
    s10 = add_base_slide("Hasil Pengujian Performa Beban Tinggi (Grafana k6)")
    add_card(s10, 0.8, 1.8, 5.6, 5.0, "Metrik 6 Skenario Pengujian", [
        "Smoke Test (2 VU, 15s): 140 req, p95 82ms, err 0.00%",
        "Load Test (10 VU, 40s): 1,070 req, p95 306ms, err 0.00%",
        "Stress Test (25 VU, 50s): 2,244 req, p95 525ms, err 0.00%",
        "Spike Test (25 VU surge): 1,458 req, p95 305ms, err 0.00%",
        "Soak Test (6 VU, 70s): 1,623 req, p95 104ms, err 0.00%",
        "Breakpoint Test (45 VU, 60s): 3,423 req, p95 505ms, err 0.00%"
    ])
    add_card(s10, 6.8, 1.8, 5.6, 5.0, "Kesimpulan Kinerja Sistem", [
        "Total Requests Teruji: 9,958 requests",
        "Tingkat Kegagalan: 0.00% (0 error dari 9,958 req)",
        "Checks Valid: 100% (9,998 checks passed)",
        "Throughput Puncak: 56.61 request per detik",
        "Resiliensi: Sistem pulih instan saat lonjakan beban mereda."
    ])

    # ── SLIDE 11: PANDUAN LIVE DEMO ──
    s11 = add_base_slide("Skenario Demonstrasi Langsung (Live Demo)")
    add_card(s11, 0.8, 1.8, 5.6, 5.0, "Langkah 1 & 2 (Customer & Admin)", [
        "Langkah 1: Login Customer Test",
        "  • Tambah motor Vario 160 (B 1234 K6).",
        "  • Buat booking servis berkala.",
        "Langkah 2: Login Admin MotoCare",
        "  • Konfirmasi booking servis.",
        "  • Periksa Auto-Assignment: Budi Mekanik terpilih."
    ])
    add_card(s11, 6.8, 1.8, 5.6, 5.0, "Langkah 3 & 4 (Mekanik & Pembayaran)", [
        "Langkah 3: Login Budi Mekanik",
        "  • Mulai servis & input diagnosis kerusakan.",
        "  • Tambah Oli MPX2 (stok berkurang dari 50 ke 49).",
        "  • Selesaikan pengerjaan servis.",
        "Langkah 4: Customer & Admin",
        "  • Customer lihat invoice & upload bukti bayar.",
        "  • Admin setujui pembayaran -> Transaksi selesai."
    ])

    # ── SLIDE 12: KESIMPULAN & STATUS KESIAPAN ──
    s12 = add_base_slide("Kesimpulan & Kesiapan Presentasi")
    add_card(s12, 0.8, 1.8, 5.6, 5.0, "Pencapaian Final Proyek", [
        "Sistem perbengkelan terintegrasi multi-role 100% selesai.",
        "Arsitektur backend Laravel 11 & Sanctum API v1 kokoh.",
        "340 automated tests lulus 100% membuktikan keandalan logika.",
        "9,958 pengujian beban k6 membuktikan ketahanan konkurensi.",
        "Dokumentasi laporan dan pembagian peran lengkap."
    ])
    add_card(s12, 6.8, 1.8, 5.6, 5.0, "Status Akhir Sistem", [
        "STATUS: READY FOR PRESENTATION",
        "Kondisi: 100% Final & Verified",
        "Zero Critical Blocker Bug",
        "Alur demo interaktif siap dipaparkan.",
        "Terima kasih kepada Dosen Penguji & Rekan Mahasiswa."
    ])

    output_path = "docs/PRESENTASI_FINAL_MOTOCARE.pptx"
    prs.save(output_path)
    print(f"Presentation successfully saved to {output_path}")

if __name__ == '__main__':
    create_deck()

