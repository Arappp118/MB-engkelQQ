<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-white leading-tight flex items-center gap-2">
                Detail Service Order
                <span class="text-mc-muted font-normal text-base font-mono">#{{ $serviceOrder->id }}</span>
            </h2>
            <a href="{{ route('service-orders.index') }}" class="btn-secondary text-xs px-3 py-1.5 flex-shrink-0">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="space-y-6 animate-fade-in max-w-4xl mx-auto">

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

        {{-- Status & Info Utama --}}
        <div class="mc-card">
            <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-bold text-white">Informasi Service Order</h3>
                @php
                    $badgeClass = match($serviceOrder->status) {
                        'pending'    => 'badge-yellow',
                        'in_progress'=> 'badge-orange',
                        'completed'  => 'badge-green',
                        default      => 'badge-gray',
                    };
                    $statusLabel = match($serviceOrder->status) {
                        'pending'    => 'Menunggu Dimulai',
                        'in_progress'=> 'Sedang Dikerjakan',
                        'completed'  => 'Selesai',
                        default      => ucfirst($serviceOrder->status),
                    };
                @endphp
                <span class="badge {{ $badgeClass }}">
                    {{ $statusLabel }}
                </span>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                @if ($serviceOrder->booking)
                    <div class="p-3.5 rounded-xl bg-mc-sidebar border border-mc-border/60">
                        <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">No. Booking</dt>
                        <dd class="font-bold font-mono text-mc-orange">
                            <a href="{{ route('bookings.show', $serviceOrder->booking) }}" class="hover:underline">
                                {{ $serviceOrder->booking->nomor_booking }}
                            </a>
                        </dd>
                    </div>
                    <div class="p-3.5 rounded-xl bg-mc-sidebar border border-mc-border/60">
                        <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Jenis Layanan</dt>
                        <dd class="font-medium text-white">
                            {{ match($serviceOrder->booking->jenis_layanan) {
                                'medical_checkup' => 'Cek Kondisi',
                                'service_rutin'   => 'Servis Rutin',
                                'perbaikan'       => 'Perbaikan',
                                default           => ucfirst($serviceOrder->booking->jenis_layanan),
                            } }}
                        </dd>
                    </div>
                @endif

                @if ($serviceOrder->mechanic)
                    <div class="p-3.5 rounded-xl bg-mc-sidebar border border-mc-border/60">
                        <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Mekanik</dt>
                        <dd class="font-medium text-white">{{ $serviceOrder->mechanic->name }}</dd>
                    </div>
                @endif

                @if ($serviceOrder->started_at)
                    <div class="p-3.5 rounded-xl bg-mc-sidebar border border-mc-border/60">
                        <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Mulai Dikerjakan</dt>
                        <dd class="font-medium text-mc-text">{{ $serviceOrder->started_at->format('d/m/Y H:i') }}</dd>
                    </div>
                @endif

                @if ($serviceOrder->completed_at)
                    <div class="p-3.5 rounded-xl bg-mc-sidebar border border-mc-border/60">
                        <dt class="text-xs font-medium text-mc-muted uppercase tracking-wider mb-1">Selesai</dt>
                        <dd class="font-medium text-mc-text">{{ $serviceOrder->completed_at->format('d/m/Y H:i') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Kendaraan & Booking --}}
        @if ($serviceOrder->booking?->vehicle)
            <div class="mc-card">
                <h3 class="font-bold text-white mb-3">Kendaraan</h3>
                <p class="text-sm font-semibold text-white">
                    {{ $serviceOrder->booking->vehicle->merk }}
                    {{ $serviceOrder->booking->vehicle->model }}
                    <span class="text-mc-muted font-normal">({{ $serviceOrder->booking->vehicle->tahun }})</span>
                </p>
                <p class="text-xs text-mc-muted mt-1">
                    <span class="font-mono font-medium text-mc-text">{{ $serviceOrder->booking->vehicle->nomor_polisi }}</span>
                    &middot; {{ ucfirst(str_replace('_', ' ', $serviceOrder->booking->vehicle->tipe_mesin)) }}
                    &middot; {{ ucfirst($serviceOrder->booking->vehicle->transmisi) }}
                </p>
            </div>
        @endif

        {{-- Keluhan Customer --}}
        @if ($serviceOrder->booking?->keluhan)
            <div class="mc-card">
                <h3 class="font-bold text-white mb-3">Keluhan Customer</h3>
                <p class="text-sm text-mc-text whitespace-pre-wrap leading-relaxed">{{ $serviceOrder->booking->keluhan }}</p>
                @if ($serviceOrder->booking->diagnosis_customer)
                    <p class="text-xs text-mc-muted mt-4 font-semibold uppercase tracking-wider">Perkiraan masalah (oleh customer):</p>
                    <p class="text-sm text-mc-text/90 mt-1 whitespace-pre-wrap">
                        {{ $serviceOrder->booking->diagnosis_customer }}
                    </p>
                @endif
            </div>
        @endif

        {{-- Diagnosis Mekanik --}}
        <div class="mc-card">
            <h3 class="font-bold text-white mb-3">Diagnosis Mekanik</h3>

            @if ($serviceOrder->diagnosis_mechanic)
                <p class="text-sm text-mc-text whitespace-pre-wrap leading-relaxed">{{ $serviceOrder->diagnosis_mechanic }}</p>
                @if ($serviceOrder->notes)
                    <p class="text-xs text-mc-muted mt-4 font-semibold uppercase tracking-wider">Catatan tambahan:</p>
                    <p class="text-sm text-mc-text/90 mt-1 whitespace-pre-wrap">{{ $serviceOrder->notes }}</p>
                @endif
            @else
                <p class="text-sm text-mc-muted italic">Belum ada diagnosis dari mekanik.</p>
            @endif

            {{-- Form diagnosis — hanya mekanik yang bisa update (policy: update) --}}
            @can('update', $serviceOrder)
                <form action="{{ route('service-orders.diagnosis', $serviceOrder) }}"
                    method="POST" class="mt-5 border-t border-mc-border pt-4">
                    @csrf
                    @method('PATCH')

                    @if ($errors->any())
                        <div class="mb-4 flex items-start gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl p-3.5 text-sm">
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div>
                            <label for="diagnosis_mechanic" class="mc-label">
                                Diagnosis Mekanik <span class="text-red-400">*</span>
                            </label>
                            <textarea id="diagnosis_mechanic" name="diagnosis_mechanic" rows="4"
                                required maxlength="2000" class="mc-textarea"
                                placeholder="Masukkan diagnosis lengkap perbaikan motor...">{{ old('diagnosis_mechanic', $serviceOrder->diagnosis_mechanic) }}</textarea>
                            <x-input-error :messages="$errors->get('diagnosis_mechanic')" class="mt-1" />
                        </div>

                        <div>
                            <label for="notes" class="mc-label">
                                Catatan Tambahan <span class="text-mc-muted text-xs">(opsional)</span>
                            </label>
                            <textarea id="notes" name="notes" rows="2" maxlength="1000"
                                class="mc-textarea" placeholder="Catatan tambahan untuk customer...">{{ old('notes', $serviceOrder->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                        </div>

                        <button type="submit" class="btn-primary">
                            Simpan Diagnosis
                        </button>
                    </div>
                </form>
            @endcan
        </div>

        {{-- Daftar Item Servis --}}
        <div class="mc-card">
            <h3 class="font-bold text-white mb-4">Tindakan & Item Servis</h3>

            @if ($serviceOrder->items->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-mc-border text-mc-muted text-xs uppercase tracking-wider">
                                <th class="pb-3 text-left font-semibold">Item</th>
                                <th class="pb-3 text-right font-semibold">Harga Satuan</th>
                                <th class="pb-3 text-right font-semibold">Qty</th>
                                <th class="pb-3 text-right font-semibold">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-mc-border/50">
                            @foreach ($serviceOrder->items as $item)
                                <tr>
                                    <td class="py-3 font-medium text-white">
                                        {{ $item->item_name_snapshot }}
                                    </td>
                                    <td class="py-3 text-right text-mc-text">
                                        Rp{{ number_format($item->price_snapshot, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 text-right text-mc-text">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="py-3 text-right font-semibold text-white">
                                        Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-mc-border">
                            <tr>
                                <td colspan="3" class="pt-3 text-right text-xs text-mc-muted">Subtotal Servis</td>
                                <td class="pt-3 text-right font-semibold text-mc-text">
                                    Rp{{ number_format($serviceOrder->subtotal ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                            @if ($serviceOrder->delivery_fee)
                                <tr>
                                    <td colspan="3" class="pt-1 text-right text-xs text-mc-muted">Biaya Antar-Jemput</td>
                                    <td class="pt-1 text-right font-semibold text-mc-text">
                                        Rp{{ number_format($serviceOrder->delivery_fee, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="pt-2 text-right font-bold text-white uppercase text-xs">Grand Total</td>
                                <td class="pt-2 text-right font-extrabold text-lg text-emerald-400">
                                    Rp{{ number_format($serviceOrder->grand_total ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <p class="text-sm text-mc-muted italic">Belum ada item yang ditambahkan.</p>
            @endif

            {{-- Form tambah item — hanya mekanik/admin yang bisa (policy: addItem) --}}
            @can('addItem', $serviceOrder)
                @if ($serviceOrder->status !== 'completed')
                    @php
                        $activeItems = \App\Models\ServiceItem::active()->orderBy('category')->orderBy('name')->get();
                    @endphp
                    <form action="{{ route('service-orders.items.store', $serviceOrder) }}"
                        method="POST" class="mt-6 border-t border-mc-border pt-4">
                        @csrf

                        <p class="text-sm font-semibold text-white mb-3">Tambah Item / Tindakan</p>

                        @error('service_item_id')
                            <p class="text-xs text-red-400 mb-2">{{ $message }}</p>
                        @enderror
                        @error('quantity')
                            <p class="text-xs text-red-400 mb-2">{{ $message }}</p>
                        @enderror

                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1">
                                <label for="service_item_id" class="mc-label">Pilih Item</label>
                                <select id="service_item_id" name="service_item_id" required class="mc-select">
                                    <option value="">-- Pilih item --</option>
                                    @foreach ($activeItems->groupBy('category_label') as $categoryLabel => $items)
                                        <optgroup label="{{ $categoryLabel }}">
                                            @foreach ($items as $si)
                                                <option value="{{ $si->id }}" @selected(old('service_item_id') == $si->id)>
                                                    {{ $si->name }}
                                                    (Rp{{ number_format($si->price, 0, ',', '.') }}
                                                    {{ $si->is_sparepart && $si->stock !== null ? '· Stok: ' . $si->stock : '' }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:w-28">
                                <label for="quantity" class="mc-label">Jumlah</label>
                                <input id="quantity" name="quantity" type="number"
                                    value="{{ old('quantity', 1) }}" min="1" max="1000" required
                                    class="mc-input">
                            </div>
                            <div class="sm:self-end">
                                <button type="submit" class="w-full sm:w-auto btn-primary">
                                    Tambah
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-mc-muted mt-2">
                            Harga ditentukan oleh sistem. Anda hanya memilih item dan jumlah.
                        </p>
                    </form>
                @endif
            @endcan
        </div>

        {{-- Aksi Workflow --}}
        <div class="mc-card">
            <h3 class="font-bold text-white mb-4">Aksi Workflow</h3>
            <div class="flex flex-wrap gap-3">

                {{-- Mulai servis — mechanic/admin, status pending --}}
                @can('update', $serviceOrder)
                    @if ($serviceOrder->status === 'pending')
                        <form action="{{ route('service-orders.start', $serviceOrder) }}"
                            method="POST"
                            onsubmit="return confirm('Mulai mengerjakan service order ini?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-primary">
                                Mulai Servis
                            </button>
                        </form>
                    @endif
                @endcan

                {{-- Selesaikan servis — mechanic/admin, status in_progress --}}
                @can('complete', $serviceOrder)
                    @if ($serviceOrder->status === 'in_progress')
                        <form action="{{ route('service-orders.complete', $serviceOrder) }}"
                            method="POST"
                            onsubmit="return confirm('Tandai servis ini sebagai selesai? Pastikan diagnosis dan item sudah diisi.');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-success">
                                Selesaikan Servis
                            </button>
                        </form>
                    @endif
                @endcan

                {{-- Bayar — customer, servis selesai dan belum ada payment --}}
                @if (auth()->user()->isCustomer() && $serviceOrder->status === 'completed' && $serviceOrder->payment)
                    <a href="{{ route('payments.show', $serviceOrder->payment) }}" class="btn-primary">
                        Lihat Pembayaran
                    </a>
                @endif

                {{-- Lihat invoice --}}
                @if ($serviceOrder->status === 'completed')
                    <a href="{{ route('invoices.show', $serviceOrder) }}" target="_blank" class="btn-secondary">
                        Lihat Invoice
                    </a>
                @endif

                @if ($serviceOrder->status === 'pending' && !auth()->user()->isCustomer())
                    <p class="text-xs text-mc-muted self-center">
                        Servis belum bisa dimulai jika booking belum dikonfirmasi.
                    </p>
                @endif
            </div>

            {{-- Form pembayaran — customer, servis selesai, belum ada payment atau ditolak --}}
            @php
                $canPay = auth()->user()->isCustomer() &&
                          $serviceOrder->status === 'completed' &&
                          (!$serviceOrder->payment || $serviceOrder->payment->status === 'rejected');
            @endphp
            @if ($canPay)
                <div class="mt-6 border-t border-mc-border pt-5">
                    <p class="text-sm font-bold text-white mb-3">Ajukan Pembayaran</p>
                    <form action="{{ route('payments.store', $serviceOrder) }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="space-y-4">
                        @csrf

                        @if ($errors->any())
                            <div class="flex items-start gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl p-3.5 text-sm">
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <p class="text-sm text-mc-muted">
                            Total tagihan:
                            <strong class="text-emerald-400 font-extrabold text-base">
                                Rp{{ number_format($serviceOrder->grand_total ?? 0, 0, ',', '.') }}
                            </strong>
                            <span class="text-xs text-mc-muted/70">(ditentukan oleh sistem)</span>
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="payment_method" class="mc-label">Metode Pembayaran <span class="text-red-400">*</span></label>
                                <select id="payment_method" name="payment_method" required class="mc-select">
                                    <option value="" disabled {{ old('payment_method') ? '' : 'selected' }}>-- Pilih metode --</option>
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                                    <option value="transfer" {{ old('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                                    <option value="qris" {{ old('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                                </select>
                                <x-input-error :messages="$errors->get('payment_method')" class="mt-1" />
                            </div>

                            <div>
                                <label for="proof" class="mc-label">Bukti Pembayaran</label>
                                <input id="proof" name="proof" type="file"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="mc-input file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-mc-card file:text-mc-text hover:file:bg-mc-border">
                                <p class="text-xs text-mc-muted mt-1">Wajib untuk Transfer/QRIS (Maks 2MB).</p>
                                <x-input-error :messages="$errors->get('proof')" class="mt-1" />
                            </div>
                        </div>

                        <div>
                            <label for="pay_notes" class="mc-label">Catatan <span class="text-mc-muted text-xs">(opsional)</span></label>
                            <input id="pay_notes" name="notes" type="text" maxlength="500"
                                value="{{ old('notes') }}" placeholder="Contoh: Titip di kasir / satpam"
                                class="mc-input">
                        </div>

                        <button type="submit" class="btn-primary">
                            {{ $serviceOrder->payment && $serviceOrder->payment->status === 'rejected' ? 'Bayar Lagi' : 'Ajukan Pembayaran' }}
                        </button>
                    </form>
                </div>
            @endif

            @if ($serviceOrder->status === 'completed' && auth()->user()->isCustomer() && $serviceOrder->payment)
                @if ($serviceOrder->payment->status === 'rejected')
                    <div class="mt-4 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400">
                        <p class="text-sm font-bold mb-1">
                            Pembayaran Anda Ditolak.
                        </p>
                        @if ($serviceOrder->payment->notes)
                            <p class="text-xs text-red-300 mb-2">Alasan: {{ $serviceOrder->payment->notes }}</p>
                        @endif
                        <p class="text-xs text-red-400/80">Silakan ajukan pembayaran kembali menggunakan form di atas.</p>
                    </div>
                @else
                    <div class="mt-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-between gap-4 flex-wrap">
                        <p class="text-sm">
                            Servis kendaraan Anda telah selesai. Status pembayaran:
                            <strong class="font-bold">{{ $serviceOrder->payment->status_label }}</strong>
                        </p>
                        <a href="{{ route('payments.show', $serviceOrder->payment) }}" class="btn-secondary text-xs px-3 py-1.5">
                            Detail Pembayaran &rarr;
                        </a>
                    </div>
                @endif
            @endif
        </div>

    </div>
</x-app-layout>
