<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Pembayaran
                <span class="text-gray-400 font-normal text-base ml-2">#{{ $payment->id }}</span>
            </h2>
            @if ($payment->serviceOrder)
                <a href="{{ route('service-orders.show', $payment->serviceOrder) }}"
                    class="text-sm text-gray-600 hover:underline">
                    &larr; Kembali ke Service Order
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-300 text-green-800 rounded-md p-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-300 text-red-800 rounded-md p-4 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Info Pembayaran --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Pembayaran</h3>
                    @php
                        $statusColor = match($payment->status) {
                            'waiting_verification' => 'bg-yellow-100 text-yellow-800',
                            'verified'             => 'bg-emerald-100 text-emerald-800',
                            'rejected'             => 'bg-red-100 text-red-800',
                            'paid'                 => 'bg-green-100 text-green-800',
                            default                => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                        {{ $payment->status_label }}
                    </span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Metode Pembayaran</dt>
                        <dd class="font-medium mt-1">{{ $payment->payment_method_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Total Tagihan</dt>
                        {{-- Amount SELALU dari database, tidak pernah dari browser --}}
                        <dd class="font-bold text-lg mt-1 text-gray-900">
                            Rp{{ number_format($payment->amount, 0, ',', '.') }}
                        </dd>
                    </div>
                    @if ($payment->paid_at)
                        <div>
                            <dt class="text-gray-500">Waktu Pembayaran</dt>
                            <dd class="font-medium mt-1">{{ $payment->paid_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                    @if ($payment->verified_at)
                        <div>
                            <dt class="text-gray-500">Waktu Verifikasi</dt>
                            <dd class="font-medium mt-1">{{ $payment->verified_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>

                {{-- Bukti Pembayaran --}}
                @if ($payment->proof_path)
                    <div class="mt-4 pt-4 border-t">
                        <p class="text-sm text-gray-500 mb-1">Bukti Pembayaran</p>
                        {{-- Hanya tampilkan info ketersediaan, tidak expose path file private --}}
                        <p class="text-sm text-emerald-700 font-medium">
                            &#10003; Bukti telah diunggah
                        </p>
                    </div>
                @elseif ($payment->payment_method !== 'cash')
                    <div class="mt-4 pt-4 border-t">
                        <p class="text-sm text-amber-600">
                            &#9888; Bukti pembayaran belum diunggah.
                        </p>
                    </div>
                @endif

                {{-- Catatan / Alasan Penolakan --}}
                @if ($payment->notes)
                    <div class="mt-4 pt-4 border-t">
                        <p class="text-sm text-gray-500 mb-1">
                            {{ $payment->status === 'rejected' ? 'Alasan Penolakan' : 'Catatan' }}
                        </p>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $payment->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Service Order terkait --}}
            @if ($payment->serviceOrder)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="font-semibold mb-3 text-gray-900">Service Order</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        @if ($payment->serviceOrder->booking)
                            <div>
                                <dt class="text-gray-500">No. Booking</dt>
                                <dd class="font-medium mt-1">
                                    <a href="{{ route('bookings.show', $payment->serviceOrder->booking) }}"
                                        class="text-indigo-600 hover:underline">
                                        {{ $payment->serviceOrder->booking->nomor_booking }}
                                    </a>
                                </dd>
                            </div>
                            @if ($payment->serviceOrder->booking->vehicle)
                                <div>
                                    <dt class="text-gray-500">Kendaraan</dt>
                                    <dd class="font-medium mt-1">
                                        {{ $payment->serviceOrder->booking->vehicle->merk }}
                                        {{ $payment->serviceOrder->booking->vehicle->model }}
                                        ({{ $payment->serviceOrder->booking->vehicle->nomor_polisi }})
                                    </dd>
                                </div>
                            @endif
                        @endif
                        <div>
                            <dt class="text-gray-500">Subtotal Servis</dt>
                            <dd class="font-medium mt-1">
                                Rp{{ number_format($payment->serviceOrder->subtotal ?? 0, 0, ',', '.') }}
                            </dd>
                        </div>
                        @if ($payment->serviceOrder->delivery_fee)
                            <div>
                                <dt class="text-gray-500">Biaya Antar-Jemput</dt>
                                <dd class="font-medium mt-1">
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
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <h3 class="font-semibold mb-4 text-gray-900">Aksi Verifikasi</h3>
                        <div class="flex flex-wrap gap-3 items-start">

                            {{-- Verify --}}
                            <form action="{{ route('payments.verify', $payment) }}"
                                method="POST"
                                onsubmit="return confirm('Verifikasi pembayaran ini?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                                    Verifikasi Pembayaran
                                </button>
                            </form>

                            {{-- Reject --}}
                            <form action="{{ route('payments.reject', $payment) }}"
                                method="POST"
                                class="flex flex-col sm:flex-row items-start gap-2">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label for="reason" class="block text-xs text-gray-500 mb-1">
                                        Alasan penolakan <span class="text-red-500">*</span>
                                    </label>
                                    <input id="reason" name="reason" type="text"
                                        required maxlength="500"
                                        placeholder="Contoh: Bukti transfer tidak jelas"
                                        value="{{ old('reason') }}"
                                        class="block w-72 border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm text-sm">
                                    @error('reason')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="sm:self-end">
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
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
                <div class="bg-white shadow-sm rounded-lg p-4">
                    @if ($payment->status === 'waiting_verification')
                        <p class="text-sm text-amber-700">
                            &#9203; Pembayaran Anda sedang menunggu verifikasi oleh admin.
                        </p>
                    @elseif ($payment->status === 'verified')
                        <p class="text-sm text-emerald-700 font-medium">
                            &#10003; Pembayaran Anda telah terverifikasi. Terima kasih!
                        </p>
                    @elseif ($payment->status === 'rejected')
                        <div class="bg-red-50 border border-red-200 rounded-md p-3">
                            <p class="text-sm text-red-700 font-medium">
                                &#10008; Pembayaran ditolak.
                            </p>
                            @if ($payment->notes)
                                <p class="text-sm text-red-600 mt-1">
                                    Alasan: {{ $payment->notes }}
                                </p>
                            @endif
                            <p class="text-sm text-gray-600 mt-2">
                                Silakan hubungi bengkel untuk informasi lebih lanjut.
                            </p>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
