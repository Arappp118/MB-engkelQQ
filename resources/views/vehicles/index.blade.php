<x-app-layout>
    <x-slot name="header">Kendaraan Saya</x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Page Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-mc-text">Kendaraan Saya</h1>
                <p class="text-sm text-mc-muted mt-0.5">Kelola semua kendaraan terdaftar</p>
            </div>
            @can('create', App\Models\Vehicle::class)
                <a href="{{ route('vehicles.create') }}" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Kendaraan
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

        {{-- Vehicle List --}}
        <div class="mc-card p-0 overflow-hidden">
            @forelse ($vehicles as $vehicle)
                <article class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 border-b border-mc-border/60 last:border-0 hover:bg-mc-sidebar/30 transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-11 h-11 rounded-xl bg-blue-500/10 flex items-center justify-center ring-1 ring-blue-500/20 flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-mc-text text-sm">{{ $vehicle->merk }} {{ $vehicle->model }} <span class="text-mc-muted font-normal">({{ $vehicle->tahun }})</span></h2>
                            <p class="text-xs text-mc-muted mt-0.5">
                                <span class="font-mono font-medium text-mc-text/80">{{ $vehicle->nomor_polisi }}</span>
                                &middot; {{ ucfirst(str_replace('_', ' ', $vehicle->tipe_mesin)) }}
                                &middot; {{ ucfirst($vehicle->transmisi) }}
                                @if ($vehicle->warna) &middot; {{ $vehicle->warna }} @endif
                            </p>
                        </div>
                    </div>
                    <nav class="flex items-center gap-2 flex-shrink-0 sm:ml-auto" aria-label="Aksi kendaraan">
                        <a href="{{ route('vehicles.show', $vehicle) }}" class="btn-secondary text-xs px-3 py-1.5">Detail</a>
                        @can('update', $vehicle)
                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn-secondary text-xs px-3 py-1.5">Edit</a>
                        @endcan
                        @can('delete', $vehicle)
                            <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus kendaraan {{ $vehicle->merk }} {{ $vehicle->model }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger text-xs px-3 py-1.5">Hapus</button>
                            </form>
                        @endcan
                    </nav>
                </article>
            @empty
                <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                    <div class="w-16 h-16 rounded-2xl bg-mc-sidebar flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-mc-muted/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-mc-text mb-1">Belum ada kendaraan</h3>
                    <p class="text-sm text-mc-muted mb-5">Daftarkan motor Anda untuk mulai memesan servis.</p>
                    @can('create', App\Models\Vehicle::class)
                        <a href="{{ route('vehicles.create') }}" class="btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Kendaraan Pertama
                        </a>
                    @endcan
                </div>
            @endforelse
        </div>

        @if ($vehicles instanceof \Illuminate\Pagination\LengthAwarePaginator && $vehicles->hasPages())
            <div>{{ $vehicles->links() }}</div>
        @endif
    </div>
</x-app-layout>
