<x-app-layout>
    <x-slot name="header">Katalog Jasa & Sparepart</x-slot>

    <div class="space-y-6 animate-fade-in">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-mc-text">Katalog Jasa & Sparepart</h1>
                <p class="text-sm text-mc-muted mt-0.5">Daftar layanan jasa dan sparepart yang tersedia</p>
            </div>
            @can('create', App\Models\ServiceItem::class)
                <a href="{{ route('service-items.create') }}" class="btn-primary w-full sm:w-auto justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Item
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

        {{-- Items List --}}
        <div class="mc-card p-0 overflow-hidden">
            @forelse ($items as $item)
                <article class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 border-b border-mc-border/60 last:border-0 hover:bg-mc-sidebar/30 transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center ring-1 flex-shrink-0 
                            {{ $item->is_sparepart ? 'bg-orange-500/10 ring-orange-500/20 text-orange-400' : 'bg-purple-500/10 ring-purple-500/20 text-purple-400' }}">
                            @if($item->is_sparepart)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h2 class="font-semibold text-mc-text text-sm flex items-center gap-2">
                                {{ $item->name }}
                                @if(!$item->is_active)
                                    <span class="px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 text-[10px] font-medium border border-red-500/20">Nonaktif</span>
                                @endif
                            </h2>
                            <p class="text-xs text-mc-muted mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="px-2 py-0.5 bg-mc-sidebar rounded text-mc-text/90 font-medium">
                                    {{ $item->category_label }}
                                </span>
                                <span>&middot;</span>
                                <span>{{ $item->is_sparepart ? 'Sparepart' : 'Jasa' }}</span>
                                <span>&middot;</span>
                                <span class="font-medium text-mc-text/90">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                @if($item->is_sparepart)
                                    <span>&middot;</span>
                                    <span class="{{ $item->stock <= 5 ? 'text-red-400 font-medium' : '' }}">
                                        Stok: {{ $item->stock }} {{ $item->unit }}
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <nav class="flex items-center gap-2 flex-shrink-0 sm:ml-auto" aria-label="Aksi item">
                        <a href="{{ route('service-items.show', $item) }}" class="btn-secondary text-xs px-3 py-1.5">Detail</a>
                        @can('update', $item)
                            <a href="{{ route('service-items.edit', $item) }}" class="btn-secondary text-xs px-3 py-1.5">Edit</a>
                        @endcan
                        @can('delete', $item)
                            @if($item->is_active)
                                <form action="{{ route('service-items.destroy', $item) }}" method="POST"
                                      onsubmit="return confirm('Yakin ingin menonaktifkan item ini?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger text-xs px-3 py-1.5">Nonaktifkan</button>
                                </form>
                            @endif
                        @endcan
                    </nav>
                </article>
            @empty
                <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                    <div class="w-16 h-16 rounded-2xl bg-mc-sidebar flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-mc-muted/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-mc-text mb-1">Belum ada item</h3>
                    <p class="text-sm text-mc-muted mb-5">Belum ada layanan jasa atau sparepart yang tersedia.</p>
                    @can('create', App\Models\ServiceItem::class)
                        <a href="{{ route('service-items.create') }}" class="btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Item Pertama
                        </a>
                    @endcan
                </div>
            @endforelse
        </div>

        @if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator && $items->hasPages())
            <div>{{ $items->links() }}</div>
        @endif
    </div>
</x-app-layout>
