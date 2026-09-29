<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Page Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-mc-text">Dashboard</h1>
                <p class="text-sm text-mc-muted mt-0.5">Selamat datang kembali, {{ auth()->user()->name }}</p>
            </div>
            @if ($unread_notifications > 0)
                <a href="{{ route('notifications.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-mc-orange/10 border border-mc-orange/30 text-mc-orange text-sm font-medium hover:bg-mc-orange/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    {{ $unread_notifications }} notifikasi baru
                </a>
            @endif
        </div>

        {{-- ── Stat Cards ─────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Kendaraan --}}
            <a href="{{ route('vehicles.index') }}" class="stat-card group block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center ring-1 ring-blue-500/20">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2"/>
                        </svg>
                    </div>
                    <span class="text-3xl font-bold text-mc-text">{{ $vehicles_count }}</span>
                </div>
                <p class="text-sm text-mc-muted">Kendaraan Terdaftar</p>
                <p class="mt-2 text-xs text-blue-400 group-hover:text-blue-300 transition-colors">Lihat semua →</p>
            </a>

            {{-- Booking Aktif --}}
            <a href="{{ route('bookings.index') }}" class="stat-card group block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center ring-1 ring-amber-500/20">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-3xl font-bold text-mc-text">{{ $active_bookings }}</span>
                </div>
                <p class="text-sm text-mc-muted">Booking Aktif</p>
                <p class="mt-2 text-xs text-amber-400 group-hover:text-amber-300 transition-colors">Lihat semua →</p>
            </a>

            {{-- Servis Aktif --}}
            <a href="{{ route('service-orders.index') }}" class="stat-card group block">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-mc-orange/10 flex items-center justify-center ring-1 ring-mc-orange/20">
                        <svg class="w-5 h-5 text-mc-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="text-3xl font-bold text-mc-text">{{ $active_service_orders }}</span>
                </div>
                <p class="text-sm text-mc-muted">Servis Aktif</p>
                <p class="mt-2 text-xs text-mc-orange group-hover:text-orange-300 transition-colors">Lihat semua →</p>
            </a>

            {{-- Payment Pending --}}
            <div class="stat-card">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center ring-1 ring-red-500/20">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <span class="text-3xl font-bold {{ $pending_payments > 0 ? 'text-red-400' : 'text-mc-text' }}">{{ $pending_payments }}</span>
                </div>
                <p class="text-sm text-mc-muted">Pembayaran Pending</p>
                @if ($pending_payments > 0)
                    <p class="mt-2 text-xs text-red-400">⚠ Perlu tindakan</p>
                @else
                    <p class="mt-2 text-xs text-green-400">✓ Semua lunas</p>
                @endif
            </div>
        </div>

        {{-- ── Main Content Grid ──────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Booking Terbaru --}}
            <div class="lg:col-span-2 mc-card">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-semibold text-mc-text">Booking Terbaru</h2>
                    <a href="{{ route('bookings.create') }}" class="btn-primary text-xs px-3 py-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Booking Baru
                    </a>
                </div>

                @forelse ($recent_bookings as $booking)
                    <a href="{{ route('bookings.show', $booking) }}"
                       class="flex items-center justify-between py-3 border-b border-mc-border/60 last:border-0 hover:bg-mc-sidebar/30 -mx-6 px-6 transition-colors group">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-mc-orange/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-mc-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-mc-text truncate">{{ $booking->nomor_booking }}</p>
                                <p class="text-xs text-mc-muted">{{ $booking->vehicle->merk ?? '—' }} {{ $booking->vehicle->model ?? '' }} &middot; {{ $booking->vehicle->nomor_polisi ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            @php
                                $statusMap = [
                                    'pending'   => ['badge-yellow', 'Pending'],
                                    'confirmed' => ['badge-blue', 'Dikonfirmasi'],
                                    'cancelled' => ['badge-red', 'Dibatalkan'],
                                    'completed' => ['badge-green', 'Selesai'],
                                ];
                                [$cls, $label] = $statusMap[$booking->status] ?? ['badge-gray', $booking->status_label];
                            @endphp
                            <span class="badge {{ $cls }}">{{ $label }}</span>
                            <svg class="w-4 h-4 text-mc-muted group-hover:text-mc-text transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-10">
                        <svg class="w-12 h-12 text-mc-muted/40 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-mc-muted text-sm">Belum ada booking.</p>
                        <a href="{{ route('bookings.create') }}" class="mt-3 inline-flex btn-primary text-xs px-4 py-2">Buat Booking Pertama</a>
                    </div>
                @endforelse

                @if ($recent_bookings->isNotEmpty())
                    <div class="mt-4 text-center">
                        <a href="{{ route('bookings.index') }}" class="text-xs text-mc-muted hover:text-mc-orange transition-colors">Lihat semua booking →</a>
                    </div>
                @endif
            </div>

            {{-- Sidebar Panel --}}
            <div class="space-y-4">

                {{-- Quick Actions --}}
                <div class="mc-card">
                    <h3 class="text-sm font-semibold text-mc-text mb-4">Aksi Cepat</h3>
                    <div class="space-y-2">
                        <a href="{{ route('bookings.create') }}"
                           class="flex items-center gap-3 p-3 rounded-lg bg-mc-sidebar hover:bg-mc-border/30 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-mc-orange/10 flex items-center justify-center">
                                <svg class="w-4 h-4 text-mc-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-mc-text">Booking Baru</p>
                                <p class="text-xs text-mc-muted">Jadwalkan servis motor</p>
                            </div>
                        </a>
                        <a href="{{ route('vehicles.create') }}"
                           class="flex items-center gap-3 p-3 rounded-lg bg-mc-sidebar hover:bg-mc-border/30 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-mc-text">Tambah Kendaraan</p>
                                <p class="text-xs text-mc-muted">Daftarkan motor baru</p>
                            </div>
                        </a>
                        <a href="{{ route('notifications.index') }}"
                           class="flex items-center gap-3 p-3 rounded-lg bg-mc-sidebar hover:bg-mc-border/30 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center relative">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                @if ($unread_notifications > 0)
                                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-mc-orange text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $unread_notifications }}</span>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-mc-text">Notifikasi</p>
                                <p class="text-xs text-mc-muted">{{ $unread_notifications > 0 ? $unread_notifications . ' belum dibaca' : 'Semua terbaca' }}</p>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Delivery Tracking --}}
                @if ($latest_delivery)
                    <div class="mc-card">
                        <h3 class="text-sm font-semibold text-mc-text mb-3">Pengiriman Terakhir</h3>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-cyan-500/10 flex items-center justify-center ring-1 ring-cyan-500/20">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2m0 0V4m0 2H9"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-mc-text">{{ ucfirst($latest_delivery->type) }}</p>
                                @php
                                    $dtStatus = [
                                        'assigned'    => ['badge-blue', 'Ditugaskan'],
                                        'in_progress' => ['badge-amber', 'Dalam Perjalanan'],
                                        'completed'   => ['badge-green', 'Selesai'],
                                    ];
                                    [$dtCls, $dtLabel] = $dtStatus[$latest_delivery->status] ?? ['badge-gray', ucfirst($latest_delivery->status)];
                                @endphp
                                <span class="badge {{ $dtCls }} text-[10px]">{{ $dtLabel }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-mc-muted">Biaya: <span class="text-mc-text font-medium">Rp{{ number_format($latest_delivery->delivery_fee, 0, ',', '.') }}</span></p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
