<x-app-layout>
    <x-slot name="header">Detail Kendaraan</x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Page Header --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('vehicles.index') }}"
               class="w-9 h-9 flex items-center justify-center rounded-lg border border-mc-border hover:bg-mc-card transition-colors text-mc-muted hover:text-mc-text flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-mc-text">{{ $vehicle->merk }} {{ $vehicle->model }}</h1>
                <p class="text-sm text-mc-muted font-mono mt-0.5">{{ $vehicle->nomor_polisi }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Vehicle Detail Card --}}
            <div class="lg:col-span-2 mc-card">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-blue-500/10 flex items-center justify-center ring-1 ring-blue-500/20">
                            <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-mc-text">{{ $vehicle->merk }} {{ $vehicle->model }}</h2>
                            <p class="text-mc-muted text-sm">Tahun {{ $vehicle->tahun }}</p>
                        </div>
                    </div>
                    @can('update', $vehicle)
                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn-secondary text-xs px-3 py-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </a>
                    @endcan
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-mc-sidebar border border-mc-border/60">
                        <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Nomor Polisi</dt>
                        <dd class="font-mono font-semibold text-mc-text text-lg">{{ $vehicle->nomor_polisi }}</dd>
                    </div>
                    <div class="p-4 rounded-xl bg-mc-sidebar border border-mc-border/60">
                        <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Tipe Mesin</dt>
                        <dd class="font-semibold text-mc-text">{{ ucfirst(str_replace('_', ' ', $vehicle->tipe_mesin)) }}</dd>
                    </div>
                    <div class="p-4 rounded-xl bg-mc-sidebar border border-mc-border/60">
                        <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Transmisi</dt>
                        <dd class="font-semibold text-mc-text">{{ ucfirst($vehicle->transmisi) }}</dd>
                    </div>
                    <div class="p-4 rounded-xl bg-mc-sidebar border border-mc-border/60">
                        <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Warna</dt>
                        <dd class="font-semibold text-mc-text">{{ $vehicle->warna ?: '—' }}</dd>
                    </div>
                    @if ($vehicle->nomor_rangka)
                        <div class="p-4 rounded-xl bg-mc-sidebar border border-mc-border/60 sm:col-span-2">
                            <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Nomor Rangka</dt>
                            <dd class="font-mono font-semibold text-mc-text">{{ $vehicle->nomor_rangka }}</dd>
                        </div>
                    @endif
                    @if ($vehicle->catatan)
                        <div class="p-4 rounded-xl bg-mc-sidebar border border-mc-border/60 sm:col-span-2">
                            <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Catatan</dt>
                            <dd class="text-sm text-mc-text/80 leading-relaxed">{{ $vehicle->catatan }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Actions Sidebar --}}
            <div class="space-y-4">
                <div class="mc-card">
                    <h3 class="text-sm font-semibold text-mc-text mb-3">Aksi</h3>
                    <div class="space-y-2">
                        @can('update', $vehicle)
                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="w-full btn-secondary justify-start text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Data Kendaraan
                            </a>
                        @endcan
                        @can('create', App\Models\Booking::class)
                            <a href="{{ route('bookings.create') }}" class="w-full btn-primary justify-start text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Booking Servis
                            </a>
                        @endcan
                        @can('delete', $vehicle)
                            <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus kendaraan {{ $vehicle->merk }} {{ $vehicle->model }}? Data tidak dapat dipulihkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full btn-danger justify-start text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Hapus Kendaraan
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        {{-- Booking History --}}
        <div class="mc-card">
            <h3 class="font-semibold text-mc-text mb-5">Riwayat Booking</h3>
            @forelse ($vehicle->bookings as $booking)
                <a href="{{ route('bookings.show', $booking) }}"
                   class="flex items-center justify-between py-3 border-b border-mc-border/60 last:border-0 hover:bg-mc-sidebar/30 -mx-6 px-6 transition-colors group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-mc-orange/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-mc-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-mc-text">{{ $booking->nomor_booking }}</p>
                            <p class="text-xs text-mc-muted">{{ $booking->tanggal ? \Carbon\Carbon::parse($booking->tanggal)->format('d M Y') : '—' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
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
                        <svg class="w-4 h-4 text-mc-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @empty
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <svg class="w-10 h-10 text-mc-muted/40 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-mc-muted">Belum ada booking untuk kendaraan ini.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-app-layout>
