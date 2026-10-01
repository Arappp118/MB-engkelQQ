<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('delivery-tasks.index') }}" class="w-8 h-8 rounded-full bg-mc-sidebar flex items-center justify-center text-mc-muted hover:text-white hover:bg-mc-border transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-white leading-tight">Detail Delivery Task</h2>
        </div>
    </x-slot>

    <div class="py-12 animate-fade-in">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Left column - Details --}}
                <div class="md:col-span-2 space-y-6">
                    <div class="mc-card p-6 space-y-6">
                        <div class="flex items-start justify-between border-b border-mc-border pb-4">
                            <div>
                                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                    {{ $deliveryTask->type === 'pickup' ? '🛵 Penjemputan' : '📦 Pengantaran' }} Motor
                                </h3>
                                <p class="text-sm text-mc-orange font-mono mt-1">Booking: #{{ $deliveryTask->booking->nomor_booking }}</p>
                            </div>
                            @php
                                $badgeClass = match($deliveryTask->status) {
                                    'pending'     => 'badge-yellow',
                                    'assigned'    => 'badge-blue',
                                    'in_progress' => 'badge-amber',
                                    'completed'   => 'badge-green',
                                    'cancelled'   => 'badge-red',
                                    default       => 'badge-gray',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} text-sm px-3 py-1">{{ $deliveryTask->status_label }}</span>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="bg-mc-sidebar rounded-xl p-4 border border-mc-border">
                                    <p class="text-xs text-mc-muted mb-1">Customer</p>
                                    <p class="text-sm font-semibold text-white">{{ $deliveryTask->booking->customer->name ?? '-' }}</p>
                                    <p class="text-xs text-mc-muted mt-1">{{ $deliveryTask->booking->customer->phone ?? '-' }}</p>
                                </div>
                                <div class="bg-mc-sidebar rounded-xl p-4 border border-mc-border">
                                    <p class="text-xs text-mc-muted mb-1">Kendaraan</p>
                                    <p class="text-sm font-semibold text-white">{{ $deliveryTask->booking->vehicle->nomor_polisi ?? '-' }}</p>
                                    <p class="text-xs text-mc-muted mt-1">{{ $deliveryTask->booking->vehicle->merk ?? '' }} {{ $deliveryTask->booking->vehicle->model ?? '' }}</p>
                                </div>
                            </div>
                            
                            <div class="bg-mc-sidebar rounded-xl p-4 border border-mc-border">
                                <p class="text-xs text-mc-muted mb-2">Alamat Tujuan</p>
                                <p class="text-sm text-white leading-relaxed">{{ $deliveryTask->address }}</p>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-mc-sidebar/50 rounded-xl p-4 border border-mc-border/50">
                                <div>
                                    <p class="text-xs text-mc-muted mb-1">Jarak</p>
                                    <p class="text-sm font-semibold text-white">{{ $deliveryTask->distance_km }} km</p>
                                </div>
                                <div>
                                    <p class="text-xs text-mc-muted mb-1">Biaya Kirim</p>
                                    <p class="text-sm font-semibold text-emerald-400">Rp{{ number_format($deliveryTask->delivery_fee, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-mc-muted mb-1">Mulai</p>
                                    <p class="text-sm font-semibold text-white">{{ $deliveryTask->started_at ? $deliveryTask->started_at->format('d/m/Y H:i') : '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-mc-muted mb-1">Selesai</p>
                                    <p class="text-sm font-semibold text-white">{{ $deliveryTask->completed_at ? $deliveryTask->completed_at->format('d/m/Y H:i') : '-' }}</p>
                                </div>
                            </div>

                            @if($deliveryTask->notes)
                                <div class="bg-mc-sidebar rounded-xl p-4 border border-mc-border">
                                    <p class="text-xs text-mc-muted mb-2">Catatan Tambahan</p>
                                    <p class="text-sm text-white leading-relaxed">{{ $deliveryTask->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Right column - Actions & Assignment --}}
                <div class="space-y-6">
                    
                    {{-- Courier Info / Assign --}}
                    <div class="mc-card p-5 space-y-4">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-2">Kurir Bertugas</h3>
                        
                        @if ($deliveryTask->courier)
                            <div class="flex items-center gap-3 p-3 bg-mc-sidebar rounded-xl border border-mc-border">
                                <div class="w-10 h-10 rounded-full bg-blue-500/15 text-blue-400 flex items-center justify-center text-lg font-bold">
                                    {{ substr($deliveryTask->courier->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-white">{{ $deliveryTask->courier->name }}</p>
                                    <p class="text-xs text-mc-muted">{{ $deliveryTask->courier->phone ?? '-' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-xl text-yellow-400 text-sm text-center">
                                Belum ada kurir yang ditugaskan
                            </div>
                        @endif

                        @can('assign', \App\Models\DeliveryTask::class)
                            @if(in_array($deliveryTask->status, ['pending', 'assigned']))
                                <form action="{{ route('delivery-tasks.assign', $deliveryTask) }}" method="POST" class="mt-4 pt-4 border-t border-mc-border">
                                    @csrf
                                    @method('PATCH')
                                    <label class="block text-xs font-semibold text-mc-muted mb-2 uppercase tracking-wide">Pilih Kurir (Admin)</label>
                                    @php
                                        // Load kurir langsung dari view agar tidak perlu mengubah controller
                                        $couriers = \App\Models\User::where('role', 'courier')->get();
                                    @endphp
                                    <div class="flex flex-col gap-3">
                                        <select name="courier_id" class="w-full bg-mc-sidebar border-mc-border text-white text-sm rounded-lg focus:border-mc-orange focus:ring-mc-orange" required>
                                            <option value="">-- Pilih Kurir --</option>
                                            @foreach($couriers as $courier)
                                                <option value="{{ $courier->id }}" {{ $deliveryTask->courier_id == $courier->id ? 'selected' : '' }}>
                                                    {{ $courier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('courier_id')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                        <button type="submit" class="btn-secondary w-full justify-center">Tugaskan Kurir</button>
                                    </div>
                                </form>
                            @endif
                        @endcan
                    </div>

                    {{-- Actions for Courier (Start / Complete) --}}
                    @can('complete', $deliveryTask)
                        @if(in_array($deliveryTask->status, ['pending', 'assigned']))
                            <form action="{{ route('delivery-tasks.start', $deliveryTask) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-primary w-full justify-center py-3 text-base">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Mulai Tugas
                                </button>
                            </form>
                        @elseif($deliveryTask->status === 'in_progress')
                            <form action="{{ route('delivery-tasks.complete', $deliveryTask) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full justify-center py-3 text-base inline-flex items-center rounded-xl font-bold transition-all shadow-lg text-white bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 focus:ring-2 focus:ring-emerald-500/50">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Selesaikan Tugas
                                </button>
                            </form>
                        @endif
                    @endcan

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
