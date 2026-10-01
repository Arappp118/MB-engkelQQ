<x-app-layout>
    <x-slot name="header">Booking Baru</x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Page Header --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('bookings.index') }}"
               class="w-9 h-9 flex items-center justify-center rounded-lg border border-mc-border hover:bg-mc-card transition-colors text-mc-muted hover:text-mc-text flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-mc-text">Booking Baru</h1>
                <p class="text-sm text-mc-muted mt-0.5">Jadwalkan servis motor Anda</p>
            </div>
        </div>

        <div class="max-w-2xl">

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="mb-6 flex items-start gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl p-4 text-sm">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-semibold mb-1">Periksa kembali data yang diisi:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @php $vehicles = auth()->user()->vehicles; @endphp

            @if ($vehicles->isEmpty())
                <div class="mc-card text-center py-12">
                    <div class="w-16 h-16 rounded-2xl bg-mc-sidebar flex items-center justify-center mb-4 mx-auto">
                        <svg class="w-8 h-8 text-mc-muted/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0 0h10m-10 0l2-1m8 1V6a1 1 0 00-1-1h-2"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-mc-text mb-2">Belum ada kendaraan terdaftar</h3>
                    <p class="text-sm text-mc-muted mb-6">Anda perlu mendaftarkan kendaraan terlebih dahulu sebelum membuat booking.</p>
                    <a href="{{ route('vehicles.create') }}" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Daftarkan Kendaraan
                    </a>
                </div>
            @else
                <div class="mc-card">
                    <form action="{{ route('bookings.store') }}" method="POST" class="space-y-5">
                        @csrf

                        {{-- Kendaraan --}}
                        <div>
                            <label for="vehicle_id" class="mc-label">Kendaraan <span class="text-red-400">*</span></label>
                            <select id="vehicle_id" name="vehicle_id" required class="mc-select">
                                <option value="">-- Pilih kendaraan --</option>
                                @foreach ($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>
                                        {{ $vehicle->merk }} {{ $vehicle->model }} — {{ $vehicle->nomor_polisi }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('vehicle_id')" class="mt-1.5" />
                        </div>

                        {{-- Tanggal & Waktu --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="tanggal" class="mc-label">Tanggal <span class="text-red-400">*</span></label>
                                <input id="tanggal" name="tanggal" type="date"
                                    class="mc-input"
                                    value="{{ old('tanggal') }}"
                                    min="{{ date('Y-m-d') }}"
                                    required />
                                <x-input-error :messages="$errors->get('tanggal')" class="mt-1.5" />
                            </div>
                            <div>
                                <label for="waktu" class="mc-label">Waktu <span class="text-red-400">*</span></label>
                                <input id="waktu" name="waktu" type="time"
                                    class="mc-input"
                                    value="{{ old('waktu') }}"
                                    required />
                                <x-input-error :messages="$errors->get('waktu')" class="mt-1.5" />
                            </div>
                        </div>

                        {{-- Jenis Layanan --}}
                        <div>
                            <label for="jenis_layanan" class="mc-label">Jenis Layanan <span class="text-red-400">*</span></label>
                            <select id="jenis_layanan" name="jenis_layanan" required class="mc-select">
                                <option value="">-- Pilih jenis layanan --</option>
                                @foreach (['medical_checkup' => 'Cek Kondisi', 'service_rutin' => 'Servis Rutin', 'perbaikan' => 'Perbaikan'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('jenis_layanan') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('jenis_layanan')" class="mt-1.5" />
                        </div>

                        {{-- Keluhan --}}
                        <div>
                            <label for="keluhan" class="mc-label">Keluhan <span class="text-red-400">*</span></label>
                            <textarea id="keluhan" name="keluhan" rows="3"
                                class="mc-textarea"
                                placeholder="Deskripsikan masalah atau keluhan yang Anda alami..."
                                required>{{ old('keluhan') }}</textarea>
                            <x-input-error :messages="$errors->get('keluhan')" class="mt-1.5" />
                        </div>

                        {{-- Diagnosis Customer --}}
                        <div>
                            <label for="diagnosis_customer" class="mc-label">
                                Perkiraan Masalah
                                <span class="text-mc-muted text-xs ml-1">(opsional)</span>
                            </label>
                            <textarea id="diagnosis_customer" name="diagnosis_customer" rows="2"
                                class="mc-textarea"
                                placeholder="Jika Anda sudah menduga penyebab masalah, tuliskan di sini...">{{ old('diagnosis_customer') }}</textarea>
                            <x-input-error :messages="$errors->get('diagnosis_customer')" class="mt-1.5" />
                        </div>

                        {{-- Antar-Jemput --}}
                        <fieldset class="p-4 rounded-xl bg-mc-sidebar border border-mc-border space-y-4">
                            <legend class="px-2 text-sm font-semibold text-mc-text -ml-2">Layanan Antar-Jemput</legend>
                            <div class="flex items-center gap-3">
                                <input type="checkbox" id="pickup_requested" name="pickup_requested" value="true"
                                    @checked(old('pickup_requested') == 'true')
                                    class="w-4 h-4 rounded border-mc-border bg-mc-bg text-mc-orange focus:ring-mc-orange focus:ring-offset-mc-bg" />
                                <label for="pickup_requested" class="text-sm text-mc-text">Minta kendaraan dijemput</label>
                            </div>
                            <div id="pickup_address_section" class="{{ old('pickup_requested') == 'true' ? '' : 'hidden' }} space-y-4">
                                <div>
                                    <label for="alamat_pickup" class="mc-label">Alamat Penjemputan <span class="text-red-400">*</span></label>
                                    <textarea id="alamat_pickup" name="alamat_pickup" rows="2"
                                        class="mc-textarea"
                                        placeholder="Masukkan alamat lengkap penjemputan...">{{ old('alamat_pickup') }}</textarea>
                                    <x-input-error :messages="$errors->get('alamat_pickup')" class="mt-1.5" />
                                </div>
                                <div>
                                    <label for="estimated_distance_km" class="mc-label">Perkiraan Jarak (km) <span class="text-red-400">*</span></label>
                                    <input id="estimated_distance_km" name="estimated_distance_km" type="number" step="0.1" min="0"
                                        class="mc-input"
                                        value="{{ old('estimated_distance_km') }}"
                                        placeholder="Contoh: 5.5" />
                                    <x-input-error :messages="$errors->get('estimated_distance_km')" class="mt-1.5" />
                                    <p class="text-xs text-mc-muted mt-1">Estimasi jarak dari bengkel ke lokasi penjemputan.</p>
                                </div>
                            </div>
                        </fieldset>

                        {{-- Submit --}}
                        <div class="flex items-center gap-3 pt-2 border-t border-mc-border">
                            <button type="submit" class="btn-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Buat Booking
                            </button>
                            <a href="{{ route('bookings.index') }}" class="btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        const pickupCheckbox = document.getElementById('pickup_requested');
        const pickupSection  = document.getElementById('pickup_address_section');
        const alamatPickup = document.getElementById('alamat_pickup');
        const estimatedDistance = document.getElementById('estimated_distance_km');

        if (pickupCheckbox && pickupSection) {
            // Function to toggle disabled state
            const toggleFields = (isChecked) => {
                pickupSection.classList.toggle('hidden', !isChecked);
                if (alamatPickup) alamatPickup.disabled = !isChecked;
                if (estimatedDistance) estimatedDistance.disabled = !isChecked;
            };

            // Init state on page load
            toggleFields(pickupCheckbox.checked);

            pickupCheckbox.addEventListener('change', function () {
                toggleFields(this.checked);
                // Clear values when unchecked
                if (!this.checked) {
                    if (alamatPickup) alamatPickup.value = '';
                    if (estimatedDistance) estimatedDistance.value = '';
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
