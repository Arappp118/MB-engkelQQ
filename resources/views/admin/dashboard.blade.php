<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">Dashboard Admin</h2>
    </x-slot>

    <div class="py-12 animate-fade-in">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Hero Section --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-mc-card via-mc-sidebar to-mc-card border border-mc-border p-6 shadow-xl">
                <div class="absolute -right-12 -top-12 w-64 h-64 bg-mc-orange/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-mc-orange/15 border border-mc-orange/30 text-mc-orange text-xs font-semibold mb-3">
                            <span class="w-2 h-2 rounded-full bg-mc-orange animate-pulse"></span>
                            Admin Panel
                        </div>
                        <h1 class="text-2xl font-extrabold text-white">Ringkasan Sistem</h1>
                        <p class="text-sm text-mc-muted mt-1">Pantau seluruh aktivitas operasional bengkel secara real-time.</p>
                    </div>
                </div>
            </div>

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                {{-- Customers --}}
                <div class="stat-card">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center ring-1 ring-blue-500/20">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $total_customers }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Total Pelanggan</p>
                </div>

                {{-- Vehicles --}}
                <div class="stat-card">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center ring-1 ring-purple-500/20">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2m0 0V4m0 2H9"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $total_vehicles }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Kendaraan</p>
                </div>

                {{-- Active Bookings --}}
                <div class="stat-card">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center ring-1 ring-amber-500/20">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $active_bookings }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Booking Aktif</p>
                </div>

                {{-- Total Bookings --}}
                <div class="stat-card">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-500/10 flex items-center justify-center ring-1 ring-gray-500/20">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $total_bookings }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Total Booking</p>
                </div>

                {{-- Service Orders --}}
                <div class="stat-card">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-mc-orange/10 flex items-center justify-center ring-1 ring-mc-orange/20">
                            <svg class="w-5 h-5 text-mc-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $total_service_orders }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Service Orders</p>
                </div>

                {{-- Payment Pending --}}
                <div class="stat-card">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center ring-1 ring-red-500/20">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $payment_pending }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Payment Pending</p>
                </div>

                {{-- Payment Verified --}}
                <div class="stat-card">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center ring-1 ring-emerald-500/20">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $payment_verified }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Payment Verified</p>
                </div>

                {{-- Delivery Task --}}
                <div class="stat-card">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center ring-1 ring-cyan-500/20">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $delivery_task_total }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Delivery Tasks</p>
                </div>

                {{-- Employees --}}
                <div class="stat-card">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-teal-500/10 flex items-center justify-center ring-1 ring-teal-500/20">
                            <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $mechanic_total + $courier_total }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Total Pegawai</p>
                    <p class="text-xs text-mc-muted mt-1">{{ $mechanic_total }} Mekanik &middot; {{ $courier_total }} Kurir</p>
                </div>

                {{-- Spareparts --}}
                <div class="stat-card">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 flex items-center justify-center ring-1 ring-rose-500/20">
                            <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $service_item_total }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Item / Sparepart</p>
                </div>
                
                {{-- Invoices --}}
                <div class="stat-card lg:col-span-2">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center ring-1 ring-green-500/20">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $invoice_total }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Total Invoice (Selesai)</p>
                </div>
            </div>

            {{-- Recent Bookings --}}
            <div class="mc-card p-0 overflow-hidden mt-6">
                <div class="flex items-center justify-between p-5 border-b border-mc-border">
                    <h3 class="font-bold text-lg text-white">Booking Terbaru</h3>
                    <a href="{{ route('bookings.index') }}" class="text-xs font-semibold text-mc-orange hover:text-mc-orange-hover">Lihat Semua &rarr;</a>
                </div>
                
                <div class="divide-y divide-mc-border/60">
                    @forelse ($recent_bookings as $booking)
                        @php
                            $badgeClass = match($booking->status) {
                                'pending'            => 'badge-yellow',
                                'confirmed'          => 'badge-blue',
                                'waiting_pickup'     => 'badge-amber',
                                'vehicle_picked_up'  => 'badge-cyan',
                                'waiting_service'    => 'badge-purple',
                                'assigned'           => 'badge-indigo',
                                'in_service'         => 'badge-orange',
                                'service_completed'  => 'badge-teal',
                                'waiting_payment'    => 'badge-amber',
                                'paid'               => 'badge-lime',
                                'ready_for_delivery' => 'badge-violet',
                                'completed'          => 'badge-green',
                                'cancelled'          => 'badge-red',
                                default              => 'badge-gray',
                            };
                        @endphp
                        <a href="{{ route('bookings.show', $booking) }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 hover:bg-mc-sidebar/50 transition-colors group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-mc-sidebar border border-mc-border flex items-center justify-center text-mc-orange group-hover:bg-mc-orange/10 group-hover:border-mc-orange/30 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-sm text-white group-hover:text-mc-orange transition-colors">#{{ $booking->nomor_booking }}</span>
                                        <span class="text-xs text-mc-text font-medium px-2 py-0.5 bg-mc-sidebar rounded">{{ $booking->customer->name ?? '-' }}</span>
                                    </div>
                                    <div class="text-xs text-mc-muted mt-1 flex items-center gap-2">
                                        <span>{{ $booking->vehicle->merk ?? 'Kendaraan' }} {{ $booking->vehicle->model ?? '' }}</span>
                                        @if($booking->vehicle?->nomor_polisi)
                                            <span class="font-mono">{{ $booking->vehicle->nomor_polisi }}</span>
                                        @endif
                                        <span>&middot;</span>
                                        <span>{{ \Carbon\Carbon::parse($booking->tanggal)->format('d M Y') }} {{ \Carbon\Carbon::parse($booking->waktu)->format('H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 justify-between sm:justify-end border-t sm:border-0 border-mc-border/40 pt-2 sm:pt-0">
                                <span class="badge {{ $badgeClass }}">{{ $booking->status_label ?? ucfirst($booking->status) }}</span>
                                <svg class="w-4 h-4 text-mc-muted group-hover:text-mc-orange group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center text-mc-muted text-sm">Belum ada booking terbaru.</div>
                    @endforelse
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
