<x-app-layout>
    <x-slot name="header">Pengaturan Tarif Ongkir</x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl p-4 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl p-4 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Hero / Info Banner --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-mc-card via-mc-sidebar to-mc-card border border-mc-border p-6 sm:p-8 shadow-xl">
            <div class="absolute -right-10 -top-10 w-56 h-56 bg-mc-orange/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-mc-orange/15 border border-mc-orange/30 flex items-center justify-center text-mc-orange flex-shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Tarif Ongkir Pengiriman</h1>
                    <p class="text-sm text-mc-muted mt-1 max-w-xl leading-relaxed">
                        Tarif ini digunakan untuk menghitung biaya antar-jemput kendaraan secara otomatis berdasarkan jarak tempuh.
                        Perubahan hanya berlaku untuk transaksi <strong class="text-mc-text">baru</strong> — fee transaksi lama tidak berubah.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Current Rate Display --}}
            <div class="space-y-6">
                {{-- Tarif Aktif Card --}}
                <div class="mc-card p-6 space-y-4">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">Tarif Aktif Sekarang</h2>
                    </div>

                    <div class="bg-mc-sidebar rounded-xl p-5 border border-mc-border text-center">
                        <p class="text-xs text-mc-muted mb-2 uppercase tracking-wider">Harga per Kilometer</p>
                        <p class="text-4xl font-extrabold text-emerald-400">
                            Rp{{ number_format($pricePerKm, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-mc-muted mt-2">per km</p>
                    </div>

                    <div class="bg-mc-sidebar/50 rounded-xl p-4 border border-mc-border/60 space-y-2 text-xs">
                        <p class="text-mc-muted font-semibold uppercase tracking-wide">Simulasi Biaya</p>
                        @foreach ([5, 10, 15, 20] as $km)
                            <div class="flex items-center justify-between py-1 border-b border-mc-border/40 last:border-0">
                                <span class="text-mc-muted">{{ $km }} km</span>
                                <span class="font-semibold text-mc-text">
                                    Rp{{ number_format(ceil(($pricePerKm * $km) / 500) * 500, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                        <p class="text-mc-muted/60 text-[11px] pt-1">* Dibulatkan ke 500 terdekat.</p>
                    </div>
                </div>

                {{-- Info Card --}}
                <div class="mc-card p-5 space-y-3 bg-gradient-to-b from-mc-card to-mc-sidebar">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/15 text-blue-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Catatan Penting</h3>
                    </div>
                    <ul class="space-y-2 text-xs text-mc-muted leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="text-mc-orange mt-0.5">•</span>
                            <span>Tarif dihitung secara <strong class="text-mc-text">server-side</strong>. Nilai dari client diabaikan.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-mc-orange mt-0.5">•</span>
                            <span>Transaksi yang sudah ada <strong class="text-mc-text">tidak akan berubah</strong> nilainya.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-mc-orange mt-0.5">•</span>
                            <span>Hanya <strong class="text-mc-text">Admin</strong> yang dapat mengubah tarif ini.</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Right: Edit Form --}}
            <div class="lg:col-span-2">
                @if(auth()->user()->isAdmin())
                    <div class="mc-card p-6 space-y-6">
                        <div class="flex items-center gap-3 border-b border-mc-border pb-4">
                            <div class="w-9 h-9 rounded-xl bg-mc-orange/10 border border-mc-orange/20 flex items-center justify-center text-mc-orange">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-white">Ubah Tarif per Kilometer</h2>
                                <p class="text-xs text-mc-muted">Masukkan tarif baru dalam Rupiah per km</p>
                            </div>
                        </div>

                        <form action="{{ route('delivery-settings.update') }}" method="POST" class="space-y-5">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-2">
                                <label for="price_per_km" class="block text-sm font-semibold text-mc-text">
                                    Tarif Baru (Rp / km) <span class="text-red-400">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                        <span class="text-mc-muted text-sm font-medium">Rp</span>
                                    </div>
                                    <input
                                        type="number"
                                        id="price_per_km"
                                        name="price_per_km"
                                        value="{{ old('price_per_km', (int) $pricePerKm) }}"
                                        min="0"
                                        step="500"
                                        required
                                        class="w-full pl-10 pr-16 py-3 bg-mc-sidebar border-mc-border text-white rounded-xl text-sm
                                               focus:border-mc-orange focus:ring-1 focus:ring-mc-orange transition-colors
                                               placeholder-mc-muted/50"
                                        placeholder="3000"
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <span class="text-mc-muted text-xs">/km</span>
                                    </div>
                                </div>
                                @error('price_per_km')
                                    <p class="text-red-400 text-xs mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                                <p class="text-xs text-mc-muted">
                                    Contoh: masukkan <code class="bg-mc-sidebar px-1.5 py-0.5 rounded text-mc-text text-[11px]">3500</code> untuk Rp3.500 per km.
                                    Biaya akhir akan dibulatkan ke kelipatan 500 terdekat.
                                </p>
                            </div>

                            {{-- Preview calculation --}}
                            <div class="bg-mc-sidebar/60 rounded-xl border border-mc-border/60 p-4 space-y-2" id="preview-box">
                                <p class="text-xs text-mc-muted font-semibold uppercase tracking-wide">Preview Biaya (berdasarkan input)</p>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs" id="preview-grid">
                                    @foreach ([5, 10, 15, 20] as $km)
                                        <div class="bg-mc-card rounded-lg p-3 text-center border border-mc-border/60">
                                            <p class="text-mc-muted text-[11px]">{{ $km }} km</p>
                                            <p class="font-bold text-white preview-val" data-km="{{ $km }}">
                                                Rp{{ number_format(ceil(($pricePerKm * $km) / 500) * 500, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <button type="submit" id="save-btn" class="btn-primary px-6 py-3 text-base">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Simpan Tarif Baru
                                </button>
                                <span class="text-xs text-mc-muted">Perubahan langsung efektif untuk booking berikutnya</span>
                            </div>
                        </form>
                    </div>
                @else
                    {{-- Viewer-only: non-admin --}}
                    <div class="mc-card p-10 flex flex-col items-center justify-center text-center gap-4 min-h-[260px]">
                        <div class="w-14 h-14 rounded-2xl bg-mc-sidebar border border-mc-border flex items-center justify-center text-mc-muted">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-white">Hanya Admin yang dapat mengubah tarif</p>
                            <p class="text-sm text-mc-muted mt-1">Anda dapat melihat tarif aktif, namun tidak dapat mengubahnya.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const input = document.getElementById('price_per_km');
            const vals  = document.querySelectorAll('.preview-val');

            if (!input || !vals.length) return;

            function fmt(n) {
                return 'Rp' + new Intl.NumberFormat('id-ID').format(Math.ceil(n / 500) * 500);
            }

            function update() {
                const rate = parseFloat(input.value) || 0;
                vals.forEach(el => {
                    const km = parseFloat(el.dataset.km);
                    el.textContent = fmt(rate * km);
                });
            }

            input.addEventListener('input', update);
        })();
    </script>
    @endpush
</x-app-layout>
