<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">Service Order</h2>
    </x-slot>

    <div class="space-y-6 animate-fade-in">
        {{-- Page Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-mc-text">Service Order</h1>
                <p class="text-sm text-mc-muted mt-0.5">Daftar pengerjaan dan status servis motor</p>
            </div>
        </div>

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

        <div class="mc-card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-mc-border bg-mc-sidebar/60">
                            <th scope="col" class="text-left px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">No. Booking</th>
                            <th scope="col" class="text-left px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">Kendaraan</th>
                            @if (auth()->user()->isAdmin() || auth()->user()->isCustomer())
                                <th scope="col" class="text-left px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">Mekanik</th>
                            @endif
                            @if (auth()->user()->isAdmin())
                                <th scope="col" class="text-left px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">Customer</th>
                            @endif
                            <th scope="col" class="text-left px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">Status</th>
                            <th scope="col" class="text-right px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">Total</th>
                            <th scope="col" class="text-right px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-mc-border/50">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-mc-sidebar/30 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-white">
                                    {{ $order->booking->nomor_booking ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-mc-text">
                                    @if ($order->booking?->vehicle)
                                        <span class="font-medium text-white">{{ $order->booking->vehicle->merk }}
                                            {{ $order->booking->vehicle->model }}</span>
                                        <br>
                                        <span class="text-xs text-mc-muted font-mono">
                                            {{ $order->booking->vehicle->nomor_polisi }}
                                        </span>
                                    @else
                                        <span class="text-mc-muted">-</span>
                                    @endif
                                </td>
                                @if (auth()->user()->isAdmin() || auth()->user()->isCustomer())
                                    <td class="px-5 py-3.5 text-mc-text">
                                        {{ $order->mechanic?->name ?? '-' }}
                                    </td>
                                @endif
                                @if (auth()->user()->isAdmin())
                                    <td class="px-5 py-3.5 text-mc-text">
                                        {{ $order->booking?->customer?->name ?? '-' }}
                                    </td>
                                @endif
                                <td class="px-5 py-3.5">
                                    @php
                                        $badgeClass = match($order->status) {
                                            'pending'    => 'badge-yellow',
                                            'in_progress'=> 'badge-orange',
                                            'completed'  => 'badge-green',
                                            default      => 'badge-gray',
                                        };
                                        $statusLabel = match($order->status) {
                                            'pending'    => 'Menunggu',
                                            'in_progress'=> 'Sedang Dikerjakan',
                                            'completed'  => 'Selesai',
                                            default      => ucfirst($order->status),
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-bold text-mc-text">
                                    @if ($order->grand_total)
                                        Rp{{ number_format($order->grand_total, 0, ',', '.') }}
                                    @else
                                        <span class="text-mc-muted font-normal">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('service-orders.show', $order) }}"
                                        class="btn-secondary text-xs px-3 py-1.5 inline-flex">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-mc-muted">
                                    <p class="text-base font-medium text-mc-text">Belum ada service order.</p>
                                    <p class="text-sm mt-1">
                                        @if (auth()->user()->isCustomer())
                                            <a href="{{ route('bookings.create') }}"
                                                class="text-mc-orange hover:underline font-semibold">Buat booking baru</a>
                                            untuk memulai servis kendaraan Anda.
                                        @else
                                            Tidak ada data saat ini.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="px-5 py-4 border-t border-mc-border">
                    {{ $orders->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
