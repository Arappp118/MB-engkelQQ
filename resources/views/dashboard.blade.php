<x-app-layout>
    <x-slot name="header">Dashboard Pelanggan</x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl p-4 text-sm animate-fade-in">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl p-4 text-sm animate-fade-in">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- ── Hero / Welcome Banner ───────────────────────────────────────── --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-mc-card via-mc-sidebar to-mc-card border border-mc-border p-6 sm:p-8 shadow-xl">
            {{-- Background decorative glow --}}
            <div class="absolute -right-12 -top-12 w-64 h-64 bg-mc-orange/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/3 -bottom-12 w-48 h-48 bg-blue-500/5 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-mc-orange/15 border border-mc-orange/30 text-mc-orange text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-mc-orange animate-pulse"></span>
                        Portal Pelanggan MotoCare
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Selamat Datang Kembali, <span class="text-mc-orange">{{ auth()->user()->name }}</span>! 👋
                    </h1>
                    <p class="text-sm text-mc-muted leading-relaxed">
                        Pantau jadwal servis berkala, estimasi perbaikan motor, status pengerjaan oleh mekanik, hingga layanan antar-jemput motor Anda secara langsung.
                    </p>

                    @if ($unread_notifications > 0)
                        <div class="pt-1">
                            <a href="{{ route('notifications.index') }}"
                               class="inline-flex items-center gap-2 text-xs font-medium text-amber-400 hover:text-amber-300 transition-colors">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                </span>
                                Anda memiliki <strong class="underline">{{ $unread_notifications }} notifikasi baru</strong> yang belum dibaca
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Action buttons on hero --}}
                <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
                    <a href="{{ route('bookings.create') }}" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Booking Servis Baru</span>
                    </a>
                    <a href="{{ route('vehicles.create') }}" class="btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <span>Tambah Motor</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Stat Cards ─────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Kendaraan Terdaftar --}}
            <a href="{{ route('vehicles.index') }}" class="stat-card group block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center ring-1 ring-blue-500/20 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2m0 0V4m0 2H9"/>
                        </svg>
                    </div>
                    <span class="text-3xl font-extrabold text-white">{{ $vehicles_count }}</span>
                </div>
                <p class="text-sm font-semibold text-mc-text">Kendaraan Terdaftar</p>
                <p class="text-xs text-mc-muted mt-0.5">Motor tersimpan di akun Anda</p>
                <div class="mt-3 pt-2.5 border-t border-mc-border/60 flex items-center justify-between text-xs text-blue-400 group-hover:text-blue-300">
                    <span>Lihat Garasi Motor</span>
                    <span class="transform group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            {{-- Booking Aktif --}}
            <a href="{{ route('bookings.index') }}" class="stat-card group block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center ring-1 ring-amber-500/20 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-3xl font-extrabold text-white">{{ $active_bookings }}</span>
                </div>
                <p class="text-sm font-semibold text-mc-text">Booking Aktif</p>
                <p class="text-xs text-mc-muted mt-0.5">Jadwal reservasi berjalan</p>
                <div class="mt-3 pt-2.5 border-t border-mc-border/60 flex items-center justify-between text-xs text-amber-400 group-hover:text-amber-300">
                    <span>Kelola Jadwal Booking</span>
                    <span class="transform group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            {{-- Servis Berjalan --}}
            <a href="{{ route('service-orders.index') }}" class="stat-card group block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-12 h-12 rounded-xl bg-mc-orange/10 flex items-center justify-center ring-1 ring-mc-orange/20 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 text-mc-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="text-3xl font-extrabold text-white">{{ $active_service_orders }}</span>
                </div>
                <p class="text-sm font-semibold text-mc-text">Servis Berjalan</p>
                <p class="text-xs text-mc-muted mt-0.5">Sedang dikerjakan bengkel</p>
                <div class="mt-3 pt-2.5 border-t border-mc-border/60 flex items-center justify-between text-xs text-mc-orange group-hover:text-orange-300">
                    <span>Tracking Pengerjaan</span>
                    <span class="transform group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            {{-- Pembayaran Pending --}}
            <a href="{{ route('service-orders.index') }}" class="stat-card group block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-12 h-12 rounded-xl {{ $pending_payments > 0 ? 'bg-red-500/10 ring-red-500/20 text-red-400' : 'bg-emerald-500/10 ring-emerald-500/20 text-emerald-400' }} flex items-center justify-center ring-1 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <span class="text-3xl font-extrabold {{ $pending_payments > 0 ? 'text-red-400' : 'text-white' }}">{{ $pending_payments }}</span>
                </div>
                <p class="text-sm font-semibold text-mc-text">Pembayaran Pending</p>
                <p class="text-xs text-mc-muted mt-0.5">Tagihan menunggu verifikasi</p>
                <div class="mt-3 pt-2.5 border-t border-mc-border/60 flex items-center justify-between text-xs {{ $pending_payments > 0 ? 'text-red-400 group-hover:text-red-300 font-semibold' : 'text-emerald-400 group-hover:text-emerald-300' }}">
                    <span>{{ $pending_payments > 0 ? '⚠ Perlu Ditindaklanjuti' : '✓ Semua Tagihan Lunas' }}</span>
                    <span class="transform group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>
        </div>

        {{-- ── Main Content Grid ──────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── Left Column: Booking Terbaru & Edukasi Layanan ──────────── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Booking Terbaru Card --}}
                <div class="mc-card p-0 overflow-hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 sm:p-6 border-b border-mc-border">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-mc-orange/15 border border-mc-orange/30 flex items-center justify-center text-mc-orange">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-bold text-lg text-white">Booking Terbaru</h2>
                                <p class="text-xs text-mc-muted">Aktivitas dan pemesanan servis motor Anda</p>
                            </div>
                        </div>
                        <a href="{{ route('bookings.create') }}" class="btn-primary text-xs px-3.5 py-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Booking Baru</span>
                        </a>
                    </div>

                    <div class="divide-y divide-mc-border/60">
                        @forelse ($recent_bookings as $booking)
                            @php
                                $statusMap = [
                                    'pending'            => ['badge-yellow', 'Menunggu Konfirmasi'],
                                    'confirmed'          => ['badge-blue', 'Dikonfirmasi'],
                                    'waiting_pickup'     => ['badge-amber', 'Menunggu Pickup'],
                                    'vehicle_picked_up'  => ['badge-cyan', 'Kendaraan Dijemput'],
                                    'waiting_service'    => ['badge-purple', 'Menunggu Servis'],
                                    'assigned'           => ['badge-indigo', 'Mekanik Ditugaskan'],
                                    'in_service'         => ['badge-orange', 'Sedang Diservis'],
                                    'service_completed'  => ['badge-teal', 'Servis Selesai'],
                                    'waiting_payment'    => ['badge-amber', 'Menunggu Pembayaran'],
                                    'paid'               => ['badge-lime', 'Dibayar'],
                                    'ready_for_delivery' => ['badge-violet', 'Siap Diantar'],
                                    'completed'          => ['badge-green', 'Selesai'],
                                    'cancelled'          => ['badge-red', 'Dibatalkan'],
                                ];
                                [$badgeClass, $statusText] = $statusMap[$booking->status] ?? ['badge-gray', $booking->status_label ?? ucfirst($booking->status)];
                            @endphp

                            <a href="{{ route('bookings.show', $booking) }}"
                               class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 hover:bg-mc-sidebar/50 transition-colors group">
                                <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-mc-sidebar border border-mc-border flex items-center justify-center flex-shrink-0 group-hover:border-mc-orange/40 group-hover:bg-mc-orange/10 transition-colors">
                                        <svg class="w-5 h-5 text-mc-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-mono font-bold text-sm text-white group-hover:text-mc-orange transition-colors">
                                                #{{ $booking->nomor_booking }}
                                            </span>
                                            @if ($booking->pickup_requested)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-cyan-500/10 text-cyan-400 ring-1 ring-cyan-500/20">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2"/>
                                                    </svg>
                                                    Antar-Jemput
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-mc-sidebar text-mc-muted border border-mc-border">
                                                {{ ucfirst(str_replace('_', ' ', $booking->jenis_layanan ?? 'Servis')) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-mc-muted flex items-center gap-2 flex-wrap">
                                            <span class="text-mc-text font-medium">{{ $booking->vehicle->merk ?? 'Motor' }} {{ $booking->vehicle->model ?? '' }}</span>
                                            @if ($booking->vehicle?->nomor_polisi)
                                                <span class="font-mono px-1.5 py-0.5 bg-black/40 rounded border border-mc-border/80 text-mc-text/90">{{ $booking->vehicle->nomor_polisi }}</span>
                                            @endif
                                            @if ($booking->tanggal)
                                                <span class="text-mc-muted/80">&middot; {{ \Carbon\Carbon::parse($booking->tanggal)->translatedFormat('d M Y') }}</span>
                                            @endif
                                            @if ($booking->waktu)
                                                <span>{{ \Carbon\Carbon::parse($booking->waktu)->format('H:i') }} WIB</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between sm:justify-end gap-3 flex-shrink-0 pt-2 sm:pt-0 border-t sm:border-0 border-mc-border/40">
                                    <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                                    <svg class="w-4 h-4 text-mc-muted group-hover:text-mc-orange group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-12 px-4">
                                <div class="w-14 h-14 rounded-2xl bg-mc-sidebar border border-mc-border flex items-center justify-center mx-auto mb-3 text-mc-muted/50">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-base font-semibold text-white">Belum Ada Riwayat Booking</h3>
                                <p class="text-xs text-mc-muted max-w-sm mx-auto mt-1 mb-4">
                                    Motor Anda butuh servis berkala atau ganti oli? Buat booking sekarang untuk mendapatkan jadwal dan mekanik terpercaya.
                                </p>
                                <a href="{{ route('bookings.create') }}" class="btn-primary text-xs px-4 py-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Buat Booking Pertama</span>
                                </a>
                            </div>
                        @endforelse
                    </div>

                    @if ($recent_bookings->isNotEmpty())
                        <div class="p-4 bg-mc-sidebar/30 border-t border-mc-border text-center">
                            <a href="{{ route('bookings.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-mc-orange hover:text-mc-orange-hover transition-colors">
                                <span>Lihat Semua Riwayat Booking</span>
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    @endif
                </div>

                {{-- ── Fitur & Layanan Bengkel Card ────────────────────────── --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="mc-card p-4 space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-orange-500/10 text-mc-orange flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-white">Servis Cepat & Presisi</h4>
                        <p class="text-xs text-mc-muted leading-relaxed">
                            Pengerjaan tune-up, ganti oli, dan servis rutin dilakukan oleh mekanik bersertifikat MotoCare.
                        </p>
                    </div>

                    <div class="mc-card p-4 space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-white">Sparepart Original</h4>
                        <p class="text-xs text-mc-muted leading-relaxed">
                            Jaminan suku cadang asli dan harga transparan tercatat detail pada invoice resmi bengkel.
                        </p>
                    </div>

                    <div class="mc-card p-4 space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-white">Antar-Jemput Motor</h4>
                        <p class="text-xs text-mc-muted leading-relaxed">
                            Layanan kurir siap menjemput motor dari lokasi Anda dan mengantarkannya kembali setelah beres.
                        </p>
                    </div>
                </div>

            </div>

            {{-- ── Right Column: Aksi Cepat, Pengiriman, & Info ───────────── --}}
            <div class="space-y-6">

                {{-- Quick Actions Card --}}
                <div class="mc-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-white tracking-wide uppercase">Aksi Cepat</h3>
                        <span class="text-[10px] text-mc-muted font-medium bg-mc-sidebar px-2 py-0.5 rounded border border-mc-border">Menu Utama</span>
                    </div>

                    <div class="space-y-2.5">
                        <a href="{{ route('bookings.create') }}"
                           class="flex items-center gap-3.5 p-3 rounded-xl bg-mc-sidebar hover:bg-mc-orange/10 border border-mc-border hover:border-mc-orange/30 transition-all duration-150 group">
                            <div class="w-9 h-9 rounded-lg bg-mc-orange/15 text-mc-orange flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-mc-text group-hover:text-white transition-colors">Booking Baru</p>
                                <p class="text-xs text-mc-muted truncate">Jadwalkan servis rutin motor</p>
                            </div>
                            <span class="text-mc-muted group-hover:text-mc-orange group-hover:translate-x-1 transition-all text-xs">&rarr;</span>
                        </a>

                        <a href="{{ route('vehicles.create') }}"
                           class="flex items-center gap-3.5 p-3 rounded-xl bg-mc-sidebar hover:bg-blue-500/10 border border-mc-border hover:border-blue-500/30 transition-all duration-150 group">
                            <div class="w-9 h-9 rounded-lg bg-blue-500/15 text-blue-400 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-mc-text group-hover:text-white transition-colors">Daftarkan Motor</p>
                                <p class="text-xs text-mc-muted truncate">Tambah kendaraan ke garasi Anda</p>
                            </div>
                            <span class="text-mc-muted group-hover:text-blue-400 group-hover:translate-x-1 transition-all text-xs">&rarr;</span>
                        </a>

                        <a href="{{ route('service-orders.index') }}"
                           class="flex items-center gap-3.5 p-3 rounded-xl bg-mc-sidebar hover:bg-emerald-500/10 border border-mc-border hover:border-emerald-500/30 transition-all duration-150 group">
                            <div class="w-9 h-9 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-mc-text group-hover:text-white transition-colors">Status & Riwayat Servis</p>
                                <p class="text-xs text-mc-muted truncate">Pantau SPK & proses mekanik</p>
                            </div>
                            <span class="text-mc-muted group-hover:text-emerald-400 group-hover:translate-x-1 transition-all text-xs">&rarr;</span>
                        </a>

                        <a href="{{ route('notifications.index') }}"
                           class="flex items-center gap-3.5 p-3 rounded-xl bg-mc-sidebar hover:bg-purple-500/10 border border-mc-border hover:border-purple-500/30 transition-all duration-150 group">
                            <div class="w-9 h-9 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center flex-shrink-0 relative group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                @if ($unread_notifications > 0)
                                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-mc-orange text-white text-[9px] font-bold rounded-full flex items-center justify-center">
                                        {{ $unread_notifications > 9 ? '9+' : $unread_notifications }}
                                    </span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-mc-text group-hover:text-white transition-colors">Notifikasi</p>
                                <p class="text-xs text-mc-muted truncate">
                                    {{ $unread_notifications > 0 ? $unread_notifications . ' belum dibaca' : 'Semua sudah terbaca' }}
                                </p>
                            </div>
                            <span class="text-mc-muted group-hover:text-purple-400 group-hover:translate-x-1 transition-all text-xs">&rarr;</span>
                        </a>
                    </div>
                </div>

                {{-- Delivery Tracking Card --}}
                <div class="mc-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-cyan-500/15 text-cyan-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2m0 0V4m0 2H9"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-white tracking-wide uppercase">Pengiriman Terakhir</h3>
                        </div>
                    </div>

                    @if ($latest_delivery)
                        @php
                            $dtStatus = [
                                'pending'     => ['badge-yellow', 'Menunggu'],
                                'assigned'    => ['badge-blue', 'Ditugaskan'],
                                'in_progress' => ['badge-amber', 'Dalam Perjalanan'],
                                'completed'   => ['badge-green', 'Selesai'],
                                'cancelled'   => ['badge-red', 'Dibatalkan'],
                            ];
                            [$dtBadgeClass, $dtBadgeText] = $dtStatus[$latest_delivery->status] ?? ['badge-gray', ucfirst($latest_delivery->status)];
                        @endphp

                        <div class="space-y-3.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-white flex items-center gap-1.5">
                                    {{ $latest_delivery->type === 'pickup' ? '🛵 Penjemputan Motor' : '📦 Pengantaran Motor' }}
                                </span>
                                <span class="badge {{ $dtBadgeClass }} text-[10px]">{{ $dtBadgeText }}</span>
                            </div>

                            <div class="bg-mc-sidebar rounded-xl p-3.5 border border-mc-border space-y-2 text-xs">
                                <div>
                                    <p class="text-mc-muted text-[11px]">Alamat Tujuan:</p>
                                    <p class="text-mc-text font-medium mt-0.5 line-clamp-2">{{ $latest_delivery->address ?? 'Alamat tersimpan' }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-mc-border/60">
                                    <div>
                                        <p class="text-mc-muted text-[11px]">Estimasi Jarak:</p>
                                        <p class="text-mc-text font-semibold">{{ $latest_delivery->distance_km ?? 0 }} km</p>
                                    </div>
                                    <div>
                                        <p class="text-mc-muted text-[11px]">Biaya Kirim:</p>
                                        <p class="text-mc-orange font-semibold">Rp{{ number_format($latest_delivery->delivery_fee, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                @if ($latest_delivery->courier)
                                    <div class="pt-2 border-t border-mc-border/60 flex items-center justify-between">
                                        <span class="text-mc-muted text-[11px]">Kurir Bertugas:</span>
                                        <span class="text-mc-text font-medium">{{ $latest_delivery->courier->name }}</span>
                                    </div>
                                @endif
                            </div>

                            @if ($latest_delivery->booking_id)
                                <a href="{{ route('bookings.show', $latest_delivery->booking_id) }}"
                                   class="inline-flex items-center justify-center w-full py-2 px-3 rounded-lg bg-mc-sidebar hover:bg-mc-card border border-mc-border text-xs font-semibold text-mc-text transition-colors">
                                    Lihat Detail Booking &rarr;
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-6 px-3 bg-mc-sidebar/50 rounded-xl border border-mc-border/60">
                            <div class="w-10 h-10 rounded-full bg-cyan-500/10 text-cyan-400 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2"/>
                                </svg>
                            </div>
                            <p class="text-xs font-semibold text-white">Layanan Antar-Jemput Siap Pakai</p>
                            <p class="text-[11px] text-mc-muted mt-1 leading-relaxed">
                                Butuh bantuan antar atau jemput motor? Centang opsi 'Antar-Jemput' saat membuat reservasi servis.
                            </p>
                            <a href="{{ route('bookings.create') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-cyan-400 hover:text-cyan-300">
                                <span>Pesan Servis Sekarang</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Operational & Emergency Contact --}}
                <div class="mc-card p-5 space-y-3.5 bg-gradient-to-b from-mc-card to-mc-sidebar">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white uppercase tracking-wider">Jam Operasional Bengkel</h4>
                            <p class="text-[11px] text-mc-muted">MotoCare Service Center</p>
                        </div>
                    </div>

                    <div class="space-y-1.5 text-xs">
                        <div class="flex items-center justify-between text-mc-muted py-1 border-b border-mc-border/40">
                            <span>Senin — Sabtu</span>
                            <span class="text-mc-text font-semibold">08.00 — 17.00 WIB</span>
                        </div>
                        <div class="flex items-center justify-between text-mc-muted py-1">
                            <span>Minggu & Hari Libur</span>
                            <span class="text-red-400 font-semibold">Tutup</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-mc-border/60">
                        <p class="text-[11px] text-mc-muted">Butuh konsultasi darurat?</p>
                        <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer"
                           class="mt-1.5 inline-flex items-center justify-center gap-2 w-full py-2 px-3 rounded-lg bg-emerald-600/15 hover:bg-emerald-600/25 border border-emerald-500/30 text-emerald-400 text-xs font-semibold transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/>
                            </svg>
                            <span>Hubungi CS Bengkel (WhatsApp)</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>
