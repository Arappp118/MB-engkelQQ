<x-app-layout>
    <x-slot name="header">Edit Item</x-slot>

    <div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-mc-text">Edit Jasa / Sparepart</h1>
                <p class="text-sm text-mc-muted mt-0.5">Ubah informasi untuk {{ $serviceItem->name }}</p>
            </div>
            <a href="{{ route('service-items.show', $serviceItem) }}" class="btn-secondary">
                Kembali
            </a>
        </div>

        <form action="{{ route('service-items.update', $serviceItem) }}" method="POST" class="mc-card p-6 space-y-6" id="itemForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Item -->
                <div class="space-y-2 md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-mc-text">Nama Item <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $serviceItem->name) }}" required
                           class="w-full bg-mc-sidebar border border-mc-border rounded-xl px-4 py-2.5 text-mc-text focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-colors">
                    @error('name') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                </div>

                <!-- Tipe Item -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-mc-text">Tipe Item <span class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_sparepart" value="0" class="text-blue-500 bg-mc-sidebar border-mc-border focus:ring-blue-500/50" {{ old('is_sparepart', $serviceItem->is_sparepart ? '1' : '0') == '0' ? 'checked' : '' }} onchange="toggleSparepartFields(false)">
                            <span class="text-mc-text">Jasa</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_sparepart" value="1" class="text-blue-500 bg-mc-sidebar border-mc-border focus:ring-blue-500/50" {{ old('is_sparepart', $serviceItem->is_sparepart ? '1' : '0') == '1' ? 'checked' : '' }} onchange="toggleSparepartFields(true)">
                            <span class="text-mc-text">Sparepart</span>
                        </label>
                    </div>
                    @error('is_sparepart') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                </div>

                <!-- Kategori -->
                <div class="space-y-2">
                    <label for="category" class="block text-sm font-medium text-mc-text">Kategori <span class="text-red-500">*</span></label>
                    <select name="category" id="category" required
                            class="w-full bg-mc-sidebar border border-mc-border rounded-xl px-4 py-2.5 text-mc-text focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-colors">
                        <option value="">Pilih Kategori</option>
                        <option value="2_tak" {{ old('category', $serviceItem->category) == '2_tak' ? 'selected' : '' }}>Mesin 2 Tak</option>
                        <option value="4_tak" {{ old('category', $serviceItem->category) == '4_tak' ? 'selected' : '' }}>Mesin 4 Tak</option>
                        <option value="kelistrikan" {{ old('category', $serviceItem->category) == 'kelistrikan' ? 'selected' : '' }}>Kelistrikan</option>
                        <option value="umum" {{ old('category', $serviceItem->category) == 'umum' ? 'selected' : '' }}>Umum</option>
                        <option value="sparepart" {{ old('category', $serviceItem->category) == 'sparepart' ? 'selected' : '' }}>Sparepart</option>
                    </select>
                    @error('category') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                </div>

                <!-- Harga -->
                <div class="space-y-2 md:col-span-2 lg:col-span-1">
                    <label for="price" class="block text-sm font-medium text-mc-text">Harga (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" id="price" value="{{ old('price', (int)$serviceItem->price) }}" required min="0" step="1"
                           class="w-full bg-mc-sidebar border border-mc-border rounded-xl px-4 py-2.5 text-mc-text focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-colors">
                    @error('price') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                </div>

                <!-- Stok & Unit (Hanya untuk Sparepart) -->
                <div class="grid grid-cols-2 gap-4 md:col-span-2 lg:col-span-1 sparepart-only" style="{{ old('is_sparepart', $serviceItem->is_sparepart ? '1' : '0') == '1' ? '' : 'display: none;' }}">
                    <div class="space-y-2">
                        <label for="stock" class="block text-sm font-medium text-mc-text">Stok <span class="text-red-500">*</span></label>
                        <input type="number" name="stock" id="stock" value="{{ old('stock', $serviceItem->stock) }}" min="0" step="1"
                               class="w-full bg-mc-sidebar border border-mc-border rounded-xl px-4 py-2.5 text-mc-text focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-colors">
                        @error('stock') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="unit" class="block text-sm font-medium text-mc-text">Satuan</label>
                        <input type="text" name="unit" id="unit" value="{{ old('unit', $serviceItem->unit) }}"
                               class="w-full bg-mc-sidebar border border-mc-border rounded-xl px-4 py-2.5 text-mc-text focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-colors">
                        @error('unit') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="space-y-2 md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-mc-text">Deskripsi</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full bg-mc-sidebar border border-mc-border rounded-xl px-4 py-2.5 text-mc-text focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-colors">{{ old('description', $serviceItem->description) }}</textarea>
                    @error('description') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                </div>

                <!-- Status Aktif -->
                <div class="space-y-2 md:col-span-2">
                    <label class="flex items-center gap-3 cursor-pointer p-4 border border-mc-border rounded-xl bg-mc-sidebar/50">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="w-5 h-5 text-blue-500 bg-mc-bg border-mc-border rounded focus:ring-blue-500/50" {{ old('is_active', $serviceItem->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                        <div>
                            <p class="text-sm font-medium text-mc-text">Item Aktif</p>
                            <p class="text-xs text-mc-muted">Item dapat digunakan dalam transaksi</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="btn-primary px-8">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function toggleSparepartFields(isSparepart) {
            const sparepartFields = document.querySelectorAll('.sparepart-only');
            const stockInput = document.getElementById('stock');
            
            sparepartFields.forEach(field => {
                if (isSparepart) {
                    field.style.display = 'grid';
                } else {
                    field.style.display = 'none';
                    stockInput.value = '';
                }
            });
        }
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            const isSparepartChecked = document.querySelector('input[name="is_sparepart"]:checked')?.value === '1';
            toggleSparepartFields(isSparepartChecked);
        });
    </script>
    @endpush
</x-app-layout>
