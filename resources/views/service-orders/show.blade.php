<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Service Order
                <span class="text-gray-400 font-normal text-base ml-2">#{{ $serviceOrder->id }}</span>
            </h2>
            <a href="{{ route('service-orders.index') }}" class="text-sm text-gray-600 hover:underline">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

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

            {{-- Status & Info Utama --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Service Order</h3>
                    @php
                        $statusColor = match($serviceOrder->status) {
                            'pending'    => 'bg-yellow-100 text-yellow-800',
                            'in_progress'=> 'bg-blue-100 text-blue-800',
                            'completed'  => 'bg-emerald-100 text-emerald-800',
                            default      => 'bg-gray-100 text-gray-700',
                        };
                        $statusLabel = match($serviceOrder->status) {
                            'pending'    => 'Menunggu Dimulai',
                            'in_progress'=> 'Sedang Dikerjakan',
                            'completed'  => 'Selesai',
                            default      => ucfirst($serviceOrder->status),
                        };
                    @endphp
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    @if ($serviceOrder->booking)
                        <div>
                            <dt class="text-gray-500">No. Booking</dt>
                            <dd class="font-medium mt-1">
                                <a href="{{ route('bookings.show', $serviceOrder->booking) }}"
                                    class="text-indigo-600 hover:underline">
                                    {{ $serviceOrder->booking->nomor_booking }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Jenis Layanan</dt>
                            <dd class="font-medium mt-1">
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
                        <div>
                            <dt class="text-gray-500">Mekanik</dt>
                            <dd class="font-medium mt-1">{{ $serviceOrder->mechanic->name }}</dd>
                        </div>
                    @endif

                    @if ($serviceOrder->started_at)
                        <div>
                            <dt class="text-gray-500">Mulai Dikerjakan</dt>
                            <dd class="font-medium mt-1">{{ $serviceOrder->started_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif

                    @if ($serviceOrder->completed_at)
                        <div>
                            <dt class="text-gray-500">Selesai</dt>
                            <dd class="font-medium mt-1">{{ $serviceOrder->completed_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Kendaraan & Booking --}}
            @if ($serviceOrder->booking?->vehicle)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="font-semibold mb-3 text-gray-900">Kendaraan</h3>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $serviceOrder->booking->vehicle->merk }}
                        {{ $serviceOrder->booking->vehicle->model }}
                        ({{ $serviceOrder->booking->vehicle->tahun }})
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $serviceOrder->booking->vehicle->nomor_polisi }}
                        &middot; {{ ucfirst(str_replace('_', ' ', $serviceOrder->booking->vehicle->tipe_mesin)) }}
                        &middot; {{ ucfirst($serviceOrder->booking->vehicle->transmisi) }}
                    </p>
                </div>
            @endif

            {{-- Keluhan Customer --}}
            @if ($serviceOrder->booking?->keluhan)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="font-semibold mb-3 text-gray-900">Keluhan Customer</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $serviceOrder->booking->keluhan }}</p>
                    @if ($serviceOrder->booking->diagnosis_customer)
                        <p class="text-xs text-gray-500 mt-3 font-medium">Perkiraan masalah (oleh customer):</p>
                        <p class="text-sm text-gray-600 mt-1 whitespace-pre-wrap">
                            {{ $serviceOrder->booking->diagnosis_customer }}
                        </p>
                    @endif
                </div>
            @endif

            {{-- Diagnosis Mekanik --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-semibold mb-3 text-gray-900">Diagnosis Mekanik</h3>

                @if ($serviceOrder->diagnosis_mechanic)
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $serviceOrder->diagnosis_mechanic }}</p>
                    @if ($serviceOrder->notes)
                        <p class="text-xs text-gray-500 mt-3 font-medium">Catatan tambahan:</p>
                        <p class="text-sm text-gray-600 mt-1 whitespace-pre-wrap">{{ $serviceOrder->notes }}</p>
                    @endif
                @else
                    <p class="text-sm text-gray-400 italic">Belum ada diagnosis dari mekanik.</p>
                @endif

                {{-- Form diagnosis — hanya mekanik yang bisa update (policy: update) --}}
                @can('update', $serviceOrder)
                    <form action="{{ route('service-orders.diagnosis', $serviceOrder) }}"
                        method="POST" class="mt-4 border-t pt-4">
                        @csrf
                        @method('PATCH')

                        @if ($errors->any())
                            <div class="mb-3 bg-red-50 border border-red-200 text-red-700 rounded-md p-3 text-sm">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div>
                            <label for="diagnosis_mechanic"
                                class="block text-sm font-medium text-gray-700 mb-1">
                                Diagnosis Mekanik <span class="text-red-500">*</span>
                            </label>
                            <textarea id="diagnosis_mechanic" name="diagnosis_mechanic" rows="4"
                                required maxlength="2000"
                                class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('diagnosis_mechanic', $serviceOrder->diagnosis_mechanic) }}</textarea>
                            <x-input-error :messages="$errors->get('diagnosis_mechanic')" class="mt-1" />
                        </div>

                        <div class="mt-3">
                            <label for="notes"
                                class="block text-sm font-medium text-gray-700 mb-1">
                                Catatan Tambahan (opsional)
                            </label>
                            <textarea id="notes" name="notes" rows="2" maxlength="1000"
                                class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('notes', $serviceOrder->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                        </div>

                        <button type="submit"
                            class="mt-3 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Simpan Diagnosis
                        </button>
                    </form>
                @endcan
            </div>

            {{-- Daftar Item Servis --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-semibold mb-4 text-gray-900">Tindakan & Item Servis</h3>

                @if ($serviceOrder->items->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-500 border-b">
                                <tr>
                                    <th class="pb-2 pr-4">Item</th>
                                    <th class="pb-2 pr-4 text-right">Harga Satuan</th>
                                    <th class="pb-2 pr-4 text-right">Qty</th>
                                    <th class="pb-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($serviceOrder->items as $item)
                                    <tr>
                                        <td class="py-2 pr-4 font-medium text-gray-800">
                                            {{ $item->item_name_snapshot }}
                                        </td>
                                        <td class="py-2 pr-4 text-right text-gray-600">
                                            Rp{{ number_format($item->price_snapshot, 0, ',', '.') }}
                                        </td>
                                        <td class="py-2 pr-4 text-right text-gray-600">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="py-2 text-right font-medium text-gray-800">
                                            Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-gray-200">
                                <tr>
                                    <td colspan="3" class="pt-3 pr-4 text-right text-sm text-gray-500">Subtotal Servis</td>
                                    <td class="pt-3 text-right font-semibold text-gray-800">
                                        Rp{{ number_format($serviceOrder->subtotal ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @if ($serviceOrder->delivery_fee)
                                    <tr>
                                        <td colspan="3" class="pt-1 pr-4 text-right text-sm text-gray-500">Biaya Antar-Jemput</td>
                                        <td class="pt-1 text-right font-semibold text-gray-800">
                                            Rp{{ number_format($serviceOrder->delivery_fee, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="3" class="pt-2 pr-4 text-right font-semibold text-gray-900">Grand Total</td>
                                    <td class="pt-2 text-right font-bold text-lg text-gray-900">
                                        Rp{{ number_format($serviceOrder->grand_total ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">Belum ada item yang ditambahkan.</p>
                @endif

                {{-- Form tambah item — hanya mekanik/admin yang bisa (policy: addItem) --}}
                @can('addItem', $serviceOrder)
                    @if ($serviceOrder->status !== 'completed')
                        @php
                            $activeItems = \App\Models\ServiceItem::active()->orderBy('category')->orderBy('name')->get();
                        @endphp
                        <form action="{{ route('service-orders.items.store', $serviceOrder) }}"
                            method="POST" class="mt-5 border-t pt-4">
                            @csrf

                            <p class="text-sm font-medium text-gray-700 mb-3">Tambah Item / Tindakan</p>

                            @error('service_item_id')
                                <p class="text-sm text-red-600 mb-2">{{ $message }}</p>
                            @enderror
                            @error('quantity')
                                <p class="text-sm text-red-600 mb-2">{{ $message }}</p>
                            @enderror

                            <div class="flex flex-col sm:flex-row gap-3">
                                <div class="flex-1">
                                    <label for="service_item_id"
                                        class="block text-xs text-gray-500 mb-1">Pilih Item</label>
                                    <select id="service_item_id" name="service_item_id" required
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="">-- Pilih item --</option>
                                        @foreach ($activeItems->groupBy('category_label') as $categoryLabel => $items)
                                            <optgroup label="{{ $categoryLabel }}">
                                                @foreach ($items as $si)
                                                    <option value="{{ $si->id }}"
                                                        @selected(old('service_item_id') == $si->id)>
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
                                    <label for="quantity"
                                        class="block text-xs text-gray-500 mb-1">Jumlah</label>
                                    <input id="quantity" name="quantity" type="number"
                                        value="{{ old('quantity', 1) }}" min="1" max="1000" required
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                </div>
                                <div class="sm:self-end">
                                    <button type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                        Tambah
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">
                                Harga ditentukan oleh sistem. Anda hanya memilih item dan jumlah.
                            </p>
                        </form>
                    @endif
                @endcan
            </div>

            {{-- Aksi Workflow --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-semibold mb-4 text-gray-900">Aksi</h3>
                <div class="flex flex-wrap gap-3">

                    {{-- Mulai servis — mechanic/admin, status pending --}}
                    @can('update', $serviceOrder)
                        @if ($serviceOrder->status === 'pending')
                            <form action="{{ route('service-orders.start', $serviceOrder) }}"
                                method="POST"
                                onsubmit="return confirm('Mulai mengerjakan service order ini?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
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
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                                    Selesaikan Servis
                                </button>
                            </form>
                        @endif
                    @endcan

                    {{-- Bayar — customer, servis selesai dan belum ada payment --}}
                    @if (auth()->user()->isCustomer() && $serviceOrder->status === 'completed' && $serviceOrder->payment)
                        <a href="{{ route('payments.show', $serviceOrder->payment) }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Lihat Pembayaran
                        </a>
                    @endif

                    {{-- Lihat invoice --}}
                    @if ($serviceOrder->status === 'completed')
                        <a href="{{ route('invoices.show', $serviceOrder) }}"
                            target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            Lihat Invoice (JSON)
                        </a>
                    @endif

                    @if ($serviceOrder->status === 'pending' && !auth()->user()->isCustomer())
                        <p class="text-xs text-gray-400 self-center">
                            Servis belum bisa dimulai jika booking belum dikonfirmasi.
                        </p>
                    @endif
                </div>

                {{-- Form pembayaran — customer, servis selesai, belum ada payment --}}
                @if (auth()->user()->isCustomer() && $serviceOrder->status === 'completed' && !$serviceOrder->payment)
                    <div class="mt-6 border-t pt-4">
                        <p class="text-sm font-medium text-gray-700 mb-3">Ajukan Pembayaran</p>
                        <form action="{{ route('payments.store', $serviceOrder) }}"
                            method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            @if ($errors->any())
                                <div class="mb-3 bg-red-50 border border-red-200 text-red-700 rounded-md p-3 text-sm">
                                    <ul class="list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <p class="text-sm text-gray-500 mb-3">
                                Total tagihan:
                                <strong class="text-gray-900">
                                    Rp{{ number_format($serviceOrder->grand_total ?? 0, 0, ',', '.') }}
                                </strong>
                                <span class="text-xs text-gray-400">(ditentukan oleh sistem)</span>
                            </p>

                            <div class="flex flex-col sm:flex-row gap-3">
                                <div class="sm:w-48">
                                    <label for="payment_method"
                                        class="block text-xs text-gray-500 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                                    <select id="payment_method" name="payment_method" required
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="" disabled {{ old('payment_method') ? '' : 'selected' }}>-- Pilih --</option>
                                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                                        <option value="transfer" {{ old('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                                        <option value="qris" {{ old('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('payment_method')" class="mt-1" />
                                </div>

                                <div class="flex-1">
                                    <label for="proof"
                                        class="block text-xs text-gray-500 mb-1">Bukti Pembayaran</label>
                                    <input id="proof" name="proof" type="file"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                                    <p class="text-xs text-gray-400 mt-1">Wajib untuk Transfer/QRIS. Format: JPG, PNG, PDF. Maks 2MB.</p>
                                    <x-input-error :messages="$errors->get('proof')" class="mt-1" />
                                </div>
                            </div>

                            <div class="mt-3">
                                <label for="pay_notes"
                                    class="block text-xs text-gray-500 mb-1">Catatan (opsional)</label>
                                <input id="pay_notes" name="notes" type="text" maxlength="500"
                                    value="{{ old('notes') }}"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            </div>

                            <button type="submit"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Ajukan Pembayaran
                            </button>
                        </form>
                    </div>
                @endif

                @if ($serviceOrder->status === 'completed' && auth()->user()->isCustomer() && $serviceOrder->payment)
                    <div class="mt-4 p-3 bg-emerald-50 rounded-md border border-emerald-200">
                        <p class="text-sm text-emerald-800">
                            Servis kendaraan Anda telah selesai.
                            Status pembayaran:
                            <strong>{{ $serviceOrder->payment->status_label }}</strong>
                            &middot;
                            <a href="{{ route('payments.show', $serviceOrder->payment) }}"
                                class="text-indigo-600 hover:underline">Detail</a>
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
