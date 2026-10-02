<x-app-layout>
    <x-slot name="header">Detail Item</x-slot>

    <div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-mc-text">Detail Jasa / Sparepart</h1>
                <p class="text-sm text-mc-muted mt-0.5">Informasi lengkap tentang item layanan</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('service-items.index') }}" class="btn-secondary">
                    Kembali
                </a>
                @can('update', $serviceItem)
                    <a href="{{ route('service-items.edit', $serviceItem) }}" class="btn-primary">
                        Edit Item
                    </a>
                @endcan
            </div>
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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 space-y-6">
                <!-- Detail Utama -->
                <div class="mc-card p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center ring-2 flex-shrink-0 mt-1
                            {{ $serviceItem->is_sparepart ? 'bg-orange-500/10 ring-orange-500/20 text-orange-400' : 'bg-purple-500/10 ring-purple-500/20 text-purple-400' }}">
                            @if($serviceItem->is_sparepart)
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            @else
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <h2 class="text-xl font-bold text-mc-text">{{ $serviceItem->name }}</h2>
                                @if($serviceItem->is_active)
                                    <span class="px-2.5 py-1 rounded-full bg-green-500/10 text-green-400 text-xs font-medium border border-green-500/20">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-red-500/10 text-red-400 text-xs font-medium border border-red-500/20">Nonaktif</span>
                                @endif
                            </div>
                            <p class="text-sm text-mc-muted mt-1">{{ $serviceItem->description ?: 'Tidak ada deskripsi' }}</p>
                            
                            <div class="mt-6 grid grid-cols-2 gap-y-4 gap-x-6">
                                <div>
                                    <p class="text-xs text-mc-muted mb-1">Tipe Item</p>
                                    <p class="font-medium text-mc-text">{{ $serviceItem->is_sparepart ? 'Sparepart' : 'Jasa' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-mc-muted mb-1">Kategori</p>
                                    <p class="font-medium text-mc-text">{{ $serviceItem->category_label }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-mc-muted mb-1">Harga</p>
                                    <p class="font-medium text-mc-text text-lg">Rp {{ number_format($serviceItem->price, 0, ',', '.') }}</p>
                                </div>
                                @if($serviceItem->is_sparepart)
                                <div>
                                    <p class="text-xs text-mc-muted mb-1">Satuan</p>
                                    <p class="font-medium text-mc-text">{{ $serviceItem->unit ?: '-' }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Status & Info -->
                <div class="mc-card p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-mc-text border-b border-mc-border/50 pb-3">Informasi Sistem</h3>
                    
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-mc-muted">Dibuat Pada</span>
                        <span class="text-mc-text font-medium">{{ $serviceItem->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-mc-muted">Terakhir Diubah</span>
                        <span class="text-mc-text font-medium">{{ $serviceItem->updated_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>

                <!-- Update Stok form (hanya untuk sparepart & user yg punya izin update) -->
                @if($serviceItem->is_sparepart)
                    <div class="mc-card p-6 space-y-4 border border-blue-500/20">
                        <h3 class="text-sm font-semibold text-mc-text border-b border-mc-border/50 pb-3 flex items-center justify-between">
                            Manajemen Stok
                            <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 text-xs">{{ $serviceItem->stock }} {{ $serviceItem->unit }}</span>
                        </h3>
                        
                        @can('update', $serviceItem)
                            <form action="{{ route('service-items.stock', $serviceItem) }}" method="POST" class="space-y-4">
                                @csrf
                                @method('PATCH')
                                
                                <div class="space-y-2">
                                    <label for="stock_update" class="block text-xs font-medium text-mc-text">Sesuaikan Stok <span class="text-red-500">*</span></label>
                                    <div class="flex gap-2">
                                        <input type="number" name="stock" id="stock_update" value="{{ old('stock', $serviceItem->stock) }}" required min="0" step="1"
                                               class="w-full bg-mc-sidebar border border-mc-border rounded-lg px-3 py-2 text-sm text-mc-text focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-colors">
                                        <button type="submit" class="btn-primary py-2 px-4 text-sm whitespace-nowrap">
                                            Update
                                        </button>
                                    </div>
                                    @error('stock') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                            </form>
                        @else
                            <div class="text-center py-4 text-mc-muted text-sm">
                                <p>Stok saat ini: <span class="font-bold text-mc-text">{{ $serviceItem->stock }}</span> {{ $serviceItem->unit }}</p>
                            </div>
                        @endcan
                    </div>
                @endif
                
                @can('delete', $serviceItem)
                    @if($serviceItem->is_active)
                        <div class="mc-card p-6 space-y-4 border border-red-500/20">
                            <h3 class="text-sm font-semibold text-red-400 border-b border-red-500/20 pb-3">Zona Bahaya</h3>
                            <p class="text-xs text-mc-muted">Menonaktifkan item akan membuatnya tidak bisa dipilih pada transaksi baru. Item tidak dihapus dari sistem.</p>
                            
                            <form action="{{ route('service-items.destroy', $serviceItem) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menonaktifkan item {{ $serviceItem->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full btn-danger py-2 justify-center">
                                    Nonaktifkan Item
                                </button>
                            </form>
                        </div>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
