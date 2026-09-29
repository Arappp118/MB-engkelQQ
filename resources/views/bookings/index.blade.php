<x-app-layout>
    <x-slot name="header">
        @if (auth()->user()->isAdmin())
            Semua Booking
        @else
            Booking Saya
        @endif
    </x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Page Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-mc-text">
                    @if (auth()->user()->isAdmin()) Semua Booking @else Booking Saya @endif
                </h1>
                <p class="text-sm text-mc-muted mt-0.5">Riwayat dan status pemesanan servis</p>
            </div>
            @can('create', App\Models\Booking::class)
                <a href="{{ route('bookings.create') }}" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Booking Baru
                </a>
            @endcan
        </div>

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

        {{-- Booking List --}}
        <div class="mc-card p-0 overflow-hidden">
            @forelse ($bookings as $booking)
                @php
                    $statusMap = [
                        'pending'            => ['badge-yellow', 'Pending'],
                        'confirmed'          => ['badge-blue', 'Dikonfirmasi'],
                        'waiting_pickup'     => ['badge-amber', 'Menunggu Pickup'],
                        'vehicle_picked_up'  => ['badge-cyan', 'Dijemput'],
                        'waiting_service'    => ['badge-purple', 'Menunggu Servis'],
                        'assigned'           => ['badge-indigo', 'Ditugaskan'],
                        'in_service'         => ['badge-orange', 'Diservis'],
                        'service_completed'  => ['badge-teal', 'Servis Selesai'],
                        'waiting_payment'    => ['badge-orange', 'Tunggu Bayar'],
                        'paid'               => ['badge-lime', 'Dibayar'],
                        'ready_for_delivery' => ['badge-violet', 'Siap Diantar'],
                        'completed'          => ['badge-green', 'Selesai'],
                        'cancelled'          => ['badge-red', 'Dibatalkan'],
                    ];
                    [$cls, $statusLabel] = $statusMap[$booking->status] ?? ['badge-gray', $booking->status_label];
                @endphp
                <a href="{{ route('bookings.show', $booking) }}"
                   class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-5 border-b border-mc-border/60 last:border-0 hover:bg-mc-sidebar/30 transition-colors group">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-mc-orange/10 flex items-center justify-center ring-1 ring-mc-orange/20 flex-shrink-0">
                            <svg class="w-5 h-5 text-mc-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-mc-text text-sm">{{ $booking->nomor_booking }}</p>
                            <p class="text-xs text-mc-muted mt-0.5 truncate">
                                {{ $booking->vehicle->merk ?? '—' }} {{ $booking->vehicle->model ?? '' }}
                                @if ($booking->vehicle)
                                    &middot; <span class="font-mono">{{ $booking->vehicle->nomor_polisi }}</span>
                                @endif
                                @if (auth()->user()->isAdmin() && isset($booking->customer))
                                    &middot; <span class="text-mc-text/70">{{ $booking->customer->name }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0 sm:ml-auto">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs text-mc-muted">{{ $booking->tanggal ? \Carbon\Carbon::parse($booking->tanggal)->format('d M Y') : '—' }}</p>
                            @if ($booking->waktu)
                                <p class="text-xs text-mc-muted/70">{{ \Carbon\Carbon::parse($booking->waktu)->format('H:i') }} WIB</p>
                            @endif
                        </div>
                        <span class="badge {{ $cls }}">{{ $statusLabel }}</span>
                        <svg class="w-4 h-4 text-mc-muted group-hover:text-mc-text transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @empty
                <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                    <div class="w-16 h-16 rounded-2xl bg-mc-sidebar flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-mc-muted/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-mc-text mb-1">Belum ada booking</h3>
                    <p class="text-sm text-mc-muted mb-5">Buat booking pertama Anda untuk mulai servis motor.</p>
                    @can('create', App\Models\Booking::class)
                        <a href="{{ route('bookings.create') }}" class="btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Buat Booking Pertama
                        </a>
                    @endcan
                </div>
            @endforelse
        </div>

        @if ($bookings instanceof \Illuminate\Pagination\LengthAwarePaginator && $bookings->hasPages())
            <div>{{ $bookings->links() }}</div>
        @endif
    </div>
</x-app-layout>
