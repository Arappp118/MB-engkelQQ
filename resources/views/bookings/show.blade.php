<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('bookings.index') }}"
               class="w-8 h-8 flex items-center justify-center rounded-lg border border-mc-border hover:bg-mc-card transition-colors text-mc-muted hover:text-mc-text">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-mc-text">Booking #{{ $booking->nomor_booking }}</h1>
                <p class="text-sm text-mc-muted mt-0.5">{{ $booking->tanggal ? $booking->tanggal->format('d M Y') : '—' }}</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 space-y-6 animate-fade-in">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl p-4 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl p-4 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Main Detail Card --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="mc-card">
                    {{-- Header: Status --}}
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <p class="text-xs text-mc-muted uppercase tracking-wider font-medium mb-2">Status Booking</p>
                            @php
                                $statusBadge = match($booking->status) {
                                    'pending'            => 'badge-yellow',
                                    'confirmed'          => 'badge-blue',
                                    'waiting_pickup'     => 'badge-amber',
                                    'vehicle_picked_up'  => 'badge-cyan',
                                    'waiting_service'    => 'badge-purple',
                                    'assigned'           => 'badge-indigo',
                                    'in_service'         => 'badge-orange',
                                    'service_completed'  => 'badge-teal',
                                    'waiting_payment'    => 'badge-orange',
                                    'paid'               => 'badge-lime',
                                    'ready_for_delivery' => 'badge-violet',
                                    'completed'          => 'badge-green',
                                    'cancelled'          => 'badge-red',
                                    default              => 'badge-gray',
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }} text-sm px-3 py-1.5">{{ $booking->status_label }}</span>
                        </div>
                        @if ($booking->jenis_layanan)
                            <span class="badge badge-gray text-xs">{{ ucfirst(str_replace('_', ' ', $booking->jenis_layanan)) }}</span>
                        @endif
                    </div>

                    {{-- Detail Grid --}}
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-mc-sidebar border border-mc-border/60">
                            <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Kendaraan</dt>
                            <dd class="font-semibold text-mc-text">{{ $booking->vehicle->merk }} {{ $booking->vehicle->model }}</dd>
                            <dd class="font-mono text-sm text-mc-muted">{{ $booking->vehicle->nomor_polisi }}</dd>
                        </div>
                        <div class="p-4 rounded-xl bg-mc-sidebar border border-mc-border/60">
                            <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Jadwal</dt>
                            <dd class="font-semibold text-mc-text">{{ $booking->tanggal ? $booking->tanggal->format('d M Y') : '—' }}</dd>
                            @if ($booking->waktu)
                                <dd class="text-sm text-mc-muted">Pukul {{ \Carbon\Carbon::parse($booking->waktu)->format('H:i') }} WIB</dd>
                            @endif
                        </div>
                        <div class="p-4 rounded-xl bg-mc-sidebar border border-mc-border/60">
                            <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Antar-Jemput</dt>
                            <dd class="font-semibold {{ $booking->pickup_requested ? 'text-mc-orange' : 'text-mc-muted' }}">
                                {{ $booking->pickup_requested ? '✓ Diminta' : 'Tidak' }}
                            </dd>
                            @if ($booking->pickup_requested && $booking->alamat_pickup)
                                <dd class="text-xs text-mc-muted mt-1">{{ $booking->alamat_pickup }}</dd>
                            @endif
                        </div>
                        @if ($booking->estimated_distance_km)
                            <div class="p-4 rounded-xl bg-mc-sidebar border border-mc-border/60">
                                <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Estimasi Jarak</dt>
                                <dd class="font-semibold text-mc-text">{{ number_format($booking->estimated_distance_km, 1) }} km</dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Keluhan --}}
                    <div class="mt-5 p-4 rounded-xl bg-mc-sidebar border border-mc-border/60">
                        <p class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-2">Keluhan</p>
                        <p class="text-sm text-mc-text leading-relaxed">{{ $booking->keluhan }}</p>
                    </div>

                    @if ($booking->diagnosis_customer)
                        <div class="mt-4 p-4 rounded-xl bg-mc-sidebar border border-mc-border/60">
                            <p class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-2">Perkiraan Masalah (Anda)</p>
                            <p class="text-sm text-mc-text leading-relaxed">{{ $booking->diagnosis_customer }}</p>
                        </div>
                    @endif

                    @if ($booking->catatan)
                        <div class="mt-4 p-4 rounded-xl bg-amber-500/5 border border-amber-500/20">
                            <p class="text-xs font-medium text-amber-400 uppercase tracking-wider mb-2">Catatan</p>
                            <p class="text-sm text-mc-text/80 leading-relaxed">{{ $booking->catatan }}</p>
                        </div>
                    @endif

                    @if ($booking->cancellation_reason)
                        <div class="mt-4 p-4 rounded-xl bg-red-500/5 border border-red-500/20">
                            <p class="text-xs font-medium text-red-400 uppercase tracking-wider mb-2">Alasan Pembatalan</p>
                            <p class="text-sm text-mc-text/80 leading-relaxed">{{ $booking->cancellation_reason }}</p>
                        </div>
                    @endif
                </div>

                {{-- Service Order Link --}}
                @if ($booking->serviceOrder)
                    <div class="mc-card">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-mc-orange/10 flex items-center justify-center ring-1 ring-mc-orange/20">
                                    <svg class="w-4 h-4 text-mc-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-mc-text">Order Servis</p>
                                    <p class="text-xs text-mc-muted">{{ $booking->serviceOrder->status }}</p>
                                </div>
                            </div>
                            <a href="{{ route('service-orders.show', $booking->serviceOrder) }}" class="btn-secondary text-xs px-3 py-1.5">
                                Lihat Detail
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Actions Sidebar --}}
            <div class="space-y-4">

                {{-- Admin: Confirm --}}
                @can('confirm', $booking)
                    @if($booking->status === 'pending')
                    <div class="mc-card">
                        <h3 class="text-sm font-semibold text-mc-text mb-3">Konfirmasi Booking</h3>
                        <form action="{{ route('bookings.confirm', $booking) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full btn-success">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Konfirmasi Booking
                            </button>
                        </form>
                    </div>
                    @endif

                    @if($booking->status === 'vehicle_picked_up')
                    <div class="mc-card">
                        <h3 class="text-sm font-semibold text-mc-text mb-3">Kendaraan Tiba</h3>
                        <form action="{{ route('bookings.arrive', $booking) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full btn-success">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Kendaraan Tiba di Bengkel
                            </button>
                        </form>
                    </div>
                    @endif
                @endcan

                {{-- Customer: Cancel --}}
                @can('cancel', $booking)
                    <div class="mc-card">
                        <h3 class="text-sm font-semibold text-mc-text mb-3">Batalkan Booking</h3>
                        <form action="{{ route('bookings.cancel', $booking) }}" method="POST"
                              x-data="{ confirm: false }"
                              @submit.prevent="if(!confirm) { confirm = true } else { $el.submit() }">
                            @csrf
                            @method('PATCH')
                            <div class="mb-3">
                                <label for="cancellation_reason" class="mc-label">Alasan pembatalan <span class="text-mc-muted text-xs">(opsional)</span></label>
                                <textarea id="cancellation_reason" name="cancellation_reason" rows="2"
                                    class="mc-textarea"
                                    placeholder="Kenapa ingin membatalkan?"></textarea>
                            </div>
                            <button type="submit" class="w-full btn-danger" onclick="return confirm('Yakin ingin membatalkan booking ini?')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Batalkan Booking
                            </button>
                        </form>
                    </div>
                @endcan

                {{-- Customer info --}}
                @if (auth()->user()->isAdmin() && $booking->customer)
                    <div class="mc-card">
                        <h3 class="text-sm font-semibold text-mc-text mb-3">Informasi Customer</h3>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-mc-orange/10 flex items-center justify-center ring-1 ring-mc-orange/20 text-mc-orange font-bold text-sm">
                                {{ strtoupper(substr($booking->customer->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-mc-text">{{ $booking->customer->name }}</p>
                                <p class="text-xs text-mc-muted">{{ $booking->customer->email }}</p>
                            </div>
                        </div>
                        @if ($booking->customer->phone)
                            <p class="mt-3 text-xs text-mc-muted">📞 {{ $booking->customer->phone }}</p>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
