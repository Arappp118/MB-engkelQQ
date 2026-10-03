<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-white leading-tight flex items-center gap-2">
                <span>Detail Pembayaran</span>
                <span class="text-mc-muted font-normal text-base font-mono">#{{ $payment->id }}</span>
            </h2>
            @if ($payment->serviceOrder)
                <a href="{{ route('service-orders.show', $payment->serviceOrder) }}"
                    class="text-sm text-mc-orange hover:text-mc-orange-hover flex items-center gap-1 transition-colors">
                    &larr; Kembali ke Service Order
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 animate-fade-in">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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

            {{-- Info Pembayaran --}}
            <div class="mc-card p-6">
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-mc-border">
                    <h3 class="text-lg font-bold text-white">Informasi Pembayaran</h3>
                    @php
                        $badgeClass = match($payment->status) {
                            'waiting_verification' => 'badge-yellow',
                            'verified'             => 'badge-green',
                            'rejected'             => 'badge-red',
                            'paid'                 => 'badge-green',
                            default                => 'badge-gray',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">
                        {{ $payment->status_label }}
                    </span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-mc-muted">Metode Pembayaran</dt>
                        <dd class="font-medium mt-1 text-mc-text">{{ $payment->payment_method_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-mc-muted">Total Tagihan</dt>
                        <dd class="font-bold text-lg mt-1 text-white">
                            Rp{{ number_format($payment->amount, 0, ',', '.') }}
                        </dd>
                    </div>
                    @if ($payment->paid_at)
                        <div>
                            <dt class="text-mc-muted">Waktu Pembayaran</dt>
                            <dd class="font-medium mt-1 text-mc-text">{{ $payment->paid_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                    @if ($payment->verified_at)
                        <div>
                            <dt class="text-mc-muted">Waktu Verifikasi</dt>
                            <dd class="font-medium mt-1 text-mc-text">{{ $payment->verified_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>

                {{-- Bukti Pembayaran --}}
                @if ($payment->proof_path)
                    <div class="mt-4 pt-4 border-t border-mc-border">
                        <p class="text-sm text-mc-muted mb-1">Bukti Pembayaran</p>
                        <p class="text-sm text-emerald-400 font-medium flex items-center gap-1">
                            <span>&#10003;</span> Bukti telah diunggah
                        </p>
                    </div>
                @elseif ($payment->payment_method !== 'cash')
                    <div class="mt-4 pt-4 border-t border-mc-border">
                        <p class="text-sm text-amber-400 flex items-center gap-1">
                            <span>&#9888;</span> Bukti pembayaran belum diunggah.
                        </p>
                    </div>
                @endif

                {{-- Catatan / Alasan Penolakan --}}
                @if ($payment->notes)
                    <div class="mt-4 pt-4 border-t border-mc-border">
                        <p class="text-sm text-mc-muted mb-1">
                            {{ $payment->status === 'rejected' ? 'Alasan Penolakan' : 'Catatan' }}
                        </p>
                        <p class="text-sm text-mc-text whitespace-pre-wrap bg-mc-sidebar p-3 rounded-lg border border-mc-border">{{ $payment->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Service Order terkait --}}
            @if ($payment->serviceOrder)
                <div class="mc-card p-6">
                    <h3 class="font-bold text-lg text-white mb-4 pb-2 border-b border-mc-border">Service Order</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        @if ($payment->serviceOrder->booking)
                            <div>
                                <dt class="text-mc-muted">No. Booking</dt>
                                <dd class="font-medium mt-1">
                                    <a href="{{ route('bookings.show', $payment->serviceOrder->booking) }}"
                                        class="text-mc-orange hover:text-mc-orange-hover font-mono font-semibold transition-colors">
                                        #{{ $payment->serviceOrder->booking->nomor_booking }}
                                    </a>
                                </dd>
                            </div>
                            @if ($payment->serviceOrder->booking->vehicle)
                                <div>
                                    <dt class="text-mc-muted">Kendaraan</dt>
                                    <dd class="font-medium mt-1 text-mc-text">
                                        {{ $payment->serviceOrder->booking->vehicle->merk }}
                                        {{ $payment->serviceOrder->booking->vehicle->model }}
                                        <span class="text-mc-muted font-mono">({{ $payment->serviceOrder->booking->vehicle->nomor_polisi }})</span>
                                    </dd>
                                </div>
                            @endif
                        @endif
                        <div>
                            <dt class="text-mc-muted">Subtotal Servis</dt>
                            <dd class="font-medium mt-1 text-mc-text">
                                Rp{{ number_format($payment->serviceOrder->subtotal ?? 0, 0, ',', '.') }}
                            </dd>
                        </div>
                        @if ($payment->serviceOrder->delivery_fee)
                            <div>
                                <dt class="text-mc-muted">Biaya Antar-Jemput</dt>
                                <dd class="font-medium mt-1 text-mc-text">
                                    Rp{{ number_format($payment->serviceOrder->delivery_fee, 0, ',', '.') }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            {{-- Aksi Admin: Verify / Reject --}}
            @can('verify', App\Models\Payment::class)
                @if ($payment->status === 'waiting_verification')
                    <div class="mc-card p-6">
                        <h3 class="font-bold text-lg text-white mb-4 pb-2 border-b border-mc-border">Aksi Verifikasi</h3>
                        <div class="flex flex-col md:flex-row gap-4 items-start">

                            {{-- Verify --}}
                            <form action="{{ route('payments.verify', $payment) }}"
                                method="POST"
                                onsubmit="return confirm('Verifikasi pembayaran ini?');"
                                class="w-full md:w-auto">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 transition-colors focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:ring-offset-mc-bg">
                                    Verifikasi Pembayaran
                                </button>
                            </form>

                            {{-- Reject --}}
                            <form action="{{ route('payments.reject', $payment) }}"
                                method="POST"
                                class="w-full md:flex-1 flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                                @csrf
                                @method('PATCH')
                                <div class="flex-1">
                                    <label for="reason" class="block text-xs text-mc-muted mb-1 font-medium">
                                        Alasan penolakan <span class="text-red-400">*</span>
                                    </label>
                                    <input id="reason" name="reason" type="text"
                                        required maxlength="500"
                                        placeholder="Contoh: Bukti transfer tidak jelas"
                                        value="{{ old('reason') }}"
                                        class="mc-input text-sm">
                                    @error('reason')
                                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <button type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition-colors focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-mc-bg">
                                        Tolak
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            @endcan

            {{-- Info status untuk customer --}}
            @if (auth()->user()->isCustomer())
                <div class="mc-card p-4">
                    @if ($payment->status === 'waiting_verification')
                        <p class="text-sm text-amber-400 flex items-center gap-2">
                            <span>&#9203;</span> Pembayaran Anda sedang menunggu verifikasi oleh admin.
                        </p>
                    @elseif ($payment->status === 'verified')
                        <p class="text-sm text-emerald-400 font-medium flex items-center gap-2">
                            <span>&#10003;</span> Pembayaran Anda telah terverifikasi. Terima kasih!
                        </p>
                    @elseif ($payment->status === 'rejected')
                        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-3">
                            <p class="text-sm text-red-400 font-medium flex items-center gap-2">
                                <span>&#10008;</span> Pembayaran ditolak.
                            </p>
                            @if ($payment->notes)
                                <p class="text-sm text-red-300 mt-1">
                                    Alasan: {{ $payment->notes }}
                                </p>
                            @endif
                            <p class="text-sm text-mc-muted mt-2">
                                Silakan hubungi bengkel untuk informasi lebih lanjut.
                            </p>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
