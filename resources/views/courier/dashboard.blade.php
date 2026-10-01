<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">Dashboard Kurir</h2>
    </x-slot>

    <div class="py-12 animate-fade-in">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Notification Banner --}}
            @if($unread_notifications > 0)
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="text-amber-400 text-sm font-medium">Anda memiliki {{ $unread_notifications }} notifikasi baru.</span>
                    </div>
                    <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-amber-400 hover:text-amber-300">Lihat Notifikasi</a>
                </div>
            @endif

            {{-- Hero Section --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-mc-card via-mc-sidebar to-mc-card border border-mc-border p-6 shadow-xl">
                <div class="absolute -right-12 -top-12 w-64 h-64 bg-mc-orange/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-mc-orange/15 border border-mc-orange/30 text-mc-orange text-xs font-semibold mb-3">
                            <span class="w-2 h-2 rounded-full bg-mc-orange animate-pulse"></span>
                            Kurir Logistik
                        </div>
                        <h1 class="text-2xl font-extrabold text-white">Halo, {{ auth()->user()->name }}</h1>
                        <p class="text-sm text-mc-muted mt-1">Lihat dan kelola tugas pengantaran & penjemputan kendaraan hari ini.</p>
                    </div>
                </div>
            </div>

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                {{-- Task Pickup --}}
                <div class="stat-card group">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center ring-1 ring-purple-500/20 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2m0 0V4m0 2H9"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $pickup_total }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Task Pickup</p>
                </div>

                {{-- Task Delivery --}}
                <div class="stat-card group">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center ring-1 ring-cyan-500/20 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $delivery_total }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Task Delivery</p>
                </div>

                {{-- Task Aktif --}}
                <div class="stat-card group">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center ring-1 ring-amber-500/20 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $active_total }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Tugas Berjalan</p>
                </div>

                {{-- Completed --}}
                <div class="stat-card group">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center ring-1 ring-emerald-500/20 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white">{{ $completed_total }}</span>
                    </div>
                    <p class="text-sm font-semibold text-mc-text">Telah Selesai</p>
                </div>
            </div>

            {{-- Active Delivery Tasks --}}
            <div class="mc-card p-0 overflow-hidden mt-6">
                <div class="flex items-center justify-between p-5 border-b border-mc-border">
                    <h3 class="font-bold text-lg text-white">Tugas Aktif</h3>
                    <a href="{{ route('delivery-tasks.index') }}" class="text-xs font-semibold text-mc-orange hover:text-mc-orange-hover">Lihat Semua &rarr;</a>
                </div>
                
                <div class="divide-y divide-mc-border/60">
                    @forelse ($active_tasks as $task)
                        @php
                            $badgeClass = match($task->status) {
                                'pending'     => 'badge-yellow',
                                'assigned'    => 'badge-blue',
                                'in_progress' => 'badge-amber',
                                'completed'   => 'badge-green',
                                'cancelled'   => 'badge-red',
                                default       => 'badge-gray',
                            };
                        @endphp
                        <a href="{{ route('delivery-tasks.show', $task) }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 hover:bg-mc-sidebar/50 transition-colors group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-mc-sidebar border border-mc-border flex items-center justify-center text-mc-orange group-hover:bg-mc-orange/10 group-hover:border-mc-orange/30 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2m0 0V4m0 2H9"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm text-white group-hover:text-mc-orange transition-colors">
                                            {{ $task->type === 'pickup' ? '🛵 Penjemputan' : '📦 Pengantaran' }}
                                        </span>
                                        <span class="text-xs text-mc-muted font-mono px-2 py-0.5 bg-mc-sidebar rounded">{{ $task->booking->vehicle->nomor_polisi ?? '-' }}</span>
                                    </div>
                                    <p class="text-xs text-emerald-400 mt-1 font-semibold">
                                        Rp{{ number_format($task->delivery_fee, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 justify-between sm:justify-end border-t sm:border-0 border-mc-border/40 pt-2 sm:pt-0">
                                <span class="badge {{ $badgeClass }}">{{ $task->status_label ?? ucfirst($task->status) }}</span>
                                <svg class="w-4 h-4 text-mc-muted group-hover:text-mc-orange group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center text-mc-muted text-sm">Tidak ada tugas pengiriman aktif saat ini.</div>
                    @endforelse
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
