<x-app-layout>
    <x-slot name="header">Pengaturan Rekening & QRIS</x-slot>

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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Konfigurasi Rekening & QRIS</h1>
                    <p class="text-sm text-mc-muted mt-1 max-w-xl leading-relaxed">
                        Kelola data rekening bank resmi dan QRIS bengkel MotoCare. Informasi ini ditampilkan kepada pelanggan saat melakukan pembayaran via transfer bank atau QRIS.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Current Active Preview --}}
            <div class="space-y-6">
                {{-- Bank Status Preview --}}
                <div class="mc-card p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/15 text-blue-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Rekening Bank</h2>
                        </div>
                        <span class="badge {{ $settings['bank_is_active'] ? 'badge-green' : 'badge-gray' }}">
                            {{ $settings['bank_is_active'] ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <div class="bg-mc-sidebar rounded-xl p-4 border border-mc-border space-y-2 text-sm">
                        <p class="text-xs text-mc-muted uppercase tracking-wider">Bank Tujuan</p>
                        <p class="font-bold text-white text-base">{{ $settings['bank_name'] }}</p>
                        <div class="pt-2 border-t border-mc-border/50">
                            <p class="text-xs text-mc-muted">Nomor Rekening</p>
                            <p class="font-mono text-mc-orange font-bold text-lg">{{ $settings['bank_account_number'] }}</p>
                            <p class="text-xs text-mc-muted mt-1">Atas Nama: <strong class="text-white">{{ $settings['bank_account_name'] }}</strong></p>
                        </div>
                    </div>
                </div>

                {{-- QRIS Status Preview --}}
                <div class="mc-card p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                            </div>
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider">QRIS Merchant</h2>
                        </div>
                        <span class="badge {{ $settings['qris_is_active'] ? 'badge-green' : 'badge-gray' }}">
                            {{ $settings['qris_is_active'] ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <div class="bg-mc-sidebar rounded-xl p-4 border border-mc-border text-center space-y-2">
                        <p class="text-xs text-mc-muted uppercase tracking-wider">Nama Merchant</p>
                        <p class="font-bold text-white">{{ $settings['qris_merchant_name'] }}</p>
                        @if ($settings['qris_image_path'])
                            <div class="pt-2 flex justify-center">
                                <img src="{{ asset('storage/' . $settings['qris_image_path']) }}" alt="QRIS" class="w-40 h-40 object-contain rounded-lg border border-mc-border bg-white p-2">
                            </div>
                        @else
                            <p class="text-xs text-mc-muted/70 italic pt-2">Belum ada gambar QRIS diunggah</p>
                        @endif
                    </div>
                </div>

                {{-- SOP Verifikasi --}}
                <div class="mc-card p-5 space-y-3 bg-gradient-to-b from-mc-card to-mc-sidebar text-xs">
                    <h3 class="font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="text-mc-orange">&#9888;</span> SOP Verifikasi Manual
                    </h3>
                    <ul class="space-y-1.5 text-mc-muted leading-relaxed">
                        <li>1. Cek mutasi rekening / dashboard merchant sebelum verifikasi.</li>
                        <li>2. Pastikan nominal transfer sama persis dengan total order.</li>
                        <li>3. Jika bukti palsu atau dana belum masuk, tolak pembayaran dengan alasan yang jelas.</li>
                    </ul>
                </div>
            </div>

            {{-- Right: Form Edit --}}
            <div class="lg:col-span-2">
                <div class="mc-card p-6 sm:p-8 space-y-6">
                    <div class="border-b border-mc-border pb-4">
                        <h2 class="text-lg font-bold text-white">Ubah Konfigurasi Pembayaran</h2>
                        <p class="text-xs text-mc-muted mt-1">Perbarui nomor rekening bank dan gambar QRIS resmi bengkel.</p>
                    </div>

                    <form action="{{ route('admin.payment-settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        {{-- Section Rekening Bank --}}
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold text-white flex items-center gap-2 border-b border-mc-border/50 pb-2">
                                <span>Rekening Bank Bengkel</span>
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="bank_name" class="mc-label">Nama Bank</label>
                                    <input type="text" id="bank_name" name="bank_name" required maxlength="100"
                                        value="{{ old('bank_name', $settings['bank_name']) }}"
                                        placeholder="Contoh: Bank Central Asia (BCA)" class="mc-input">
                                    <x-input-error :messages="$errors->get('bank_name')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="bank_account_number" class="mc-label">Nomor Rekening</label>
                                    <input type="text" id="bank_account_number" name="bank_account_number" required maxlength="50"
                                        value="{{ old('bank_account_number', $settings['bank_account_number']) }}"
                                        placeholder="Contoh: 8830123456" class="mc-input font-mono">
                                    <x-input-error :messages="$errors->get('bank_account_number')" class="mt-1" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="bank_account_name" class="mc-label">Nama Pemilik Rekening</label>
                                    <input type="text" id="bank_account_name" name="bank_account_name" required maxlength="100"
                                        value="{{ old('bank_account_name', $settings['bank_account_name']) }}"
                                        placeholder="Contoh: Bengkel MotoCare" class="mc-input">
                                    <x-input-error :messages="$errors->get('bank_account_name')" class="mt-1" />
                                </div>

                                <div class="flex items-center gap-3 pt-6">
                                    <input type="hidden" name="bank_is_active" value="0">
                                    <input type="checkbox" id="bank_is_active" name="bank_is_active" value="1"
                                        {{ old('bank_is_active', $settings['bank_is_active']) ? 'checked' : '' }}
                                        class="rounded bg-mc-sidebar border-mc-border text-mc-orange focus:ring-mc-orange">
                                    <label for="bank_is_active" class="text-sm font-medium text-white cursor-pointer">
                                        Aktifkan Metode Transfer Bank
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Section QRIS --}}
                        <div class="space-y-4 pt-4 border-t border-mc-border">
                            <h3 class="text-sm font-bold text-white flex items-center gap-2 border-b border-mc-border/50 pb-2">
                                <span>QRIS Bengkel</span>
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="qris_merchant_name" class="mc-label">Nama Merchant QRIS</label>
                                    <input type="text" id="qris_merchant_name" name="qris_merchant_name" required maxlength="100"
                                        value="{{ old('qris_merchant_name', $settings['qris_merchant_name']) }}"
                                        placeholder="Contoh: MotoCare Official" class="mc-input">
                                    <x-input-error :messages="$errors->get('qris_merchant_name')" class="mt-1" />
                                </div>

                                <div class="flex items-center gap-3 pt-6">
                                    <input type="hidden" name="qris_is_active" value="0">
                                    <input type="checkbox" id="qris_is_active" name="qris_is_active" value="1"
                                        {{ old('qris_is_active', $settings['qris_is_active']) ? 'checked' : '' }}
                                        class="rounded bg-mc-sidebar border-mc-border text-mc-orange focus:ring-mc-orange">
                                    <label for="qris_is_active" class="text-sm font-medium text-white cursor-pointer">
                                        Aktifkan Metode QRIS
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label for="qris_image" class="mc-label">Unggah Gambar QRIS Resmi (JPG/PNG, Max 2MB)</label>
                                <input type="file" id="qris_image" name="qris_image" accept="image/jpeg,image/png,image/jpg" class="mc-input">
                                <x-input-error :messages="$errors->get('qris_image')" class="mt-1" />
                                <p class="text-xs text-mc-muted mt-1">Kosongkan jika tidak ingin mengganti gambar QRIS saat ini.</p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-mc-border flex items-center justify-end gap-3">
                            <button type="submit" class="btn-primary">
                                Simpan Konfigurasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
