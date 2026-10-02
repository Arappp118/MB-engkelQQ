<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">Daftar Delivery Tasks</h2>
        </div>
    </x-slot>

    <div class="py-12 animate-fade-in">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl p-4 text-sm mb-6">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl p-4 text-sm mb-6">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="mc-card p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-mc-sidebar text-mc-muted text-xs uppercase tracking-wider border-b border-mc-border">
                                <th class="px-6 py-4 font-semibold">Tugas & Booking</th>
                                <th class="px-6 py-4 font-semibold">Alamat Tujuan</th>
                                <th class="px-6 py-4 font-semibold">Jarak & Biaya</th>
                                <th class="px-6 py-4 font-semibold">Kurir</th>
                                <th class="px-6 py-4 font-semibold text-center">Status</th>
                                <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-mc-border/60">
                            @forelse ($tasks as $task)
                                @php
                                    $badgeClass = match($task->status) {
                                        'pending'     => 'badge-yellow',
                                        'assigned'    => 'badge-blue',
                                        'in_progress' => 'badge-amber',
                                        'completed'   => 'badge-green',
                                        'cancelled'   => 'badge-red',
                                        default       => 'badge-gray',
                                    };
                                @endphp
                                <tr class="hover:bg-mc-sidebar/30 transition-colors group">
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex flex-col gap-1">
                                            <span class="font-bold text-white text-sm">
                                                {{ $task->type === 'pickup' ? '🛵 Penjemputan' : '📦 Pengantaran' }}
                                            </span>
                                            <span class="text-xs text-mc-orange font-mono">
                                                #{{ $task->booking->nomor_booking }}
                                            </span>
                                            <span class="text-xs text-mc-muted mt-1">
                                                Kendaraan: <strong class="text-mc-text">{{ $task->booking->vehicle->nomor_polisi ?? '-' }}</strong>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-top max-w-[200px]">
                                        <p class="text-xs text-mc-text line-clamp-3">
                                            {{ $task->address }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex flex-col gap-1 text-xs">
                                            <span class="text-mc-text font-semibold">{{ $task->distance_km }} km</span>
                                            <span class="text-emerald-400">Rp{{ number_format($task->delivery_fee, 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        @if ($task->courier)
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-blue-500/15 text-blue-400 flex items-center justify-center text-xs font-bold">
                                                    {{ substr($task->courier->name, 0, 1) }}
                                                </div>
                                                <span class="text-xs text-mc-text font-medium">{{ $task->courier->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-mc-muted italic">Belum ditugaskan</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 align-top text-center">
                                        <span class="badge {{ $badgeClass }}">{{ $task->status_label }}</span>
                                    </td>
                                    <td class="px-6 py-4 align-top text-right space-y-2">
                                        <a href="{{ route('delivery-tasks.show', $task) }}" class="btn-secondary text-xs px-3 py-1.5 inline-flex">
                                            Detail &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-mc-muted">
                                        <div class="flex flex-col items-center justify-center gap-3">
                                            <svg class="w-10 h-10 text-mc-muted/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2"/>
                                            </svg>
                                            <span class="text-sm">Tidak ada delivery task yang ditemukan.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($tasks->hasPages())
                    <div class="p-4 border-t border-mc-border">
                        {{ $tasks->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
