<x-app-layout>
    <x-slot name="header">Manajemen Pembayaran</x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-mc-text">Manajemen Pembayaran</h1>
                <p class="text-sm text-mc-muted mt-0.5">Verifikasi dan kelola pembayaran dari pelanggan.</p>
            </div>
            @if ($pendingCount > 0)
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm font-semibold">
                    <span class="w-2 h-2 rounded-full bg-red-400 animate-pulse"></span>
                    {{ $pendingCount }} Menunggu Verifikasi
                </div>
            @endif
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl p-4 text-sm">
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

        {{-- Filter Tabs --}}
        <div class="mc-card p-1.5">
            <div class="flex flex-wrap gap-1">
                @foreach ([
                    'waiting_verification' => ['label' => 'Menunggu Verifikasi', 'color' => 'amber'],
                    'verified'             => ['label' => 'Terverifikasi',        'color' => 'emerald'],
                    'rejected'             => ['label' => 'Ditolak',              'color' => 'red'],
                    'all'                  => ['label' => 'Semua',                'color' => 'gray'],
                ] as $val => $cfg)
                    @php
                        $isActive = $status === $val;
                    @endphp
                    <a href="{{ route('payments.index', ['status' => $val]) }}"
                       class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors
                              {{ $isActive
                                   ? 'bg-mc-orange text-white shadow'
                                   : 'text-mc-muted hover:text-mc-text hover:bg-mc-sidebar' }}">
                        {{ $cfg['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Payment List --}}
        <div class="mc-card p-0 overflow-hidden">
            @if ($payments->isEmpty())
                <div class="py-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-mc-sidebar flex items-center justify-center mb-4 mx-auto">
                        <svg class="w-8 h-8 text-mc-muted/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-mc-muted text-sm">Tidak ada pembayaran dengan status ini.</p>
                </div>
            @else
                {{-- Desktop Table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-mc-border">
                                <th class="px-5 py-4 text-left text-xs font-semibold text-mc-muted uppercase tracking-wider">ID / Pelanggan</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold text-mc-muted uppercase tracking-wider">Booking</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold text-mc-muted uppercase tracking-wider">Metode</th>
                                <th class="px-5 py-4 text-right text-xs font-semibold text-mc-muted uppercase tracking-wider">Jumlah</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold text-mc-muted uppercase tracking-wider">Status</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold text-mc-muted uppercase tracking-wider">Tanggal</th>
                                <th class="px-5 py-4 text-right text-xs font-semibold text-mc-muted uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-mc-border/60">
                            @foreach ($payments as $payment)
                                @php
                                    $badgeClass = match($payment->status) {
                                        'waiting_verification' => 'badge-yellow',
                                        'verified'             => 'badge-green',
                                        'rejected'             => 'badge-red',
                                        'paid'                 => 'badge-green',
                                        default                => 'badge-gray',
                                    };
                                @endphp
                                <tr class="hover:bg-mc-sidebar/40 transition-colors">
                                    <td class="px-5 py-4">
                                        <span class="font-mono font-bold text-mc-text">#{{ $payment->id }}</span>
                                        @if ($payment->serviceOrder?->booking?->customer)
                                            <p class="text-xs text-mc-muted mt-0.5">{{ $payment->serviceOrder->booking->customer->name }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($payment->serviceOrder?->booking)
                                            <a href="{{ route('bookings.show', $payment->serviceOrder->booking) }}"
                                               class="font-mono text-mc-orange hover:text-mc-orange-hover text-xs font-semibold">
                                                #{{ $payment->serviceOrder->booking->nomor_booking }}
                                            </a>
                                            @if ($payment->serviceOrder->booking->vehicle)
                                                <p class="text-xs text-mc-muted mt-0.5">
                                                    {{ $payment->serviceOrder->booking->vehicle->merk }}
                                                    {{ $payment->serviceOrder->booking->vehicle->model }}
                                                </p>
                                            @endif
                                        @else
                                            <span class="text-mc-muted text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-mc-text font-medium">{{ $payment->payment_method_label }}</span>
                                        @if ($payment->proof_path)
                                            <p class="text-xs text-emerald-400 mt-0.5">&#10003; Bukti diunggah</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right font-bold text-mc-text">
                                        Rp{{ number_format($payment->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="badge {{ $badgeClass }}">{{ $payment->status_label }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-xs text-mc-muted">
                                        {{ $payment->paid_at?->format('d M Y H:i') ?? '—' }}
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('payments.show', $payment) }}"
                                               class="px-3 py-1.5 rounded-lg text-xs font-semibold text-mc-muted bg-mc-sidebar border border-mc-border hover:text-mc-text hover:bg-mc-card transition-colors">
                                                Detail
                                            </a>
                                            @if ($payment->status === 'waiting_verification')
                                                {{-- Verify --}}
                                                <form action="{{ route('payments.verify', $payment) }}" method="POST"
                                                      onsubmit="return confirm('Verifikasi pembayaran #{{ $payment->id }}?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                                                        Verifikasi
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="md:hidden divide-y divide-mc-border/60">
                    @foreach ($payments as $payment)
                        @php
                            $badgeClass = match($payment->status) {
                                'waiting_verification' => 'badge-yellow',
                                'verified'             => 'badge-green',
                                'rejected'             => 'badge-red',
                                default                => 'badge-gray',
                            };
                        @endphp
                        <div class="p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-mono font-bold text-mc-text">#{{ $payment->id }}</span>
                                    @if ($payment->serviceOrder?->booking?->customer)
                                        <span class="text-xs text-mc-muted ml-2">{{ $payment->serviceOrder->booking->customer->name }}</span>
                                    @endif
                                </div>
                                <span class="badge {{ $badgeClass }}">{{ $payment->status_label }}</span>
                            </div>
                            <div class="text-sm text-mc-text font-bold">Rp{{ number_format($payment->amount, 0, ',', '.') }}</div>
                            <div class="text-xs text-mc-muted">{{ $payment->payment_method_label }} &middot; {{ $payment->paid_at?->format('d M Y H:i') ?? '—' }}</div>
                            <div class="flex gap-2">
                                <a href="{{ route('payments.show', $payment) }}"
                                   class="flex-1 text-center px-3 py-2 rounded-lg text-xs font-semibold text-mc-muted bg-mc-sidebar border border-mc-border hover:text-mc-text transition-colors">
                                    Detail / Verifikasi
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($payments->hasPages())
                    <div class="px-5 py-4 border-t border-mc-border">
                        {{ $payments->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</x-app-layout>
