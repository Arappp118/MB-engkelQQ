@php $v = $vehicle ?? null; @endphp

{{-- Nomor Polisi --}}
<div>
    <label for="nomor_polisi" class="mc-label">Nomor Polisi <span class="text-red-400">*</span></label>
    <input id="nomor_polisi" name="nomor_polisi" type="text"
        class="mc-input font-mono uppercase"
        value="{{ old('nomor_polisi', $v->nomor_polisi ?? '') }}"
        placeholder="cth: B 1234 ABC"
        required autofocus />
    <x-input-error :messages="$errors->get('nomor_polisi')" class="mt-1.5" />
</div>

<div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="merk" class="mc-label">Merk <span class="text-red-400">*</span></label>
        <input id="merk" name="merk" type="text"
            class="mc-input"
            value="{{ old('merk', $v->merk ?? '') }}"
            placeholder="cth: Honda, Yamaha"
            required />
        <x-input-error :messages="$errors->get('merk')" class="mt-1.5" />
    </div>
    <div>
        <label for="model" class="mc-label">Model <span class="text-red-400">*</span></label>
        <input id="model" name="model" type="text"
            class="mc-input"
            value="{{ old('model', $v->model ?? '') }}"
            placeholder="cth: Beat, Vario, Mio"
            required />
        <x-input-error :messages="$errors->get('model')" class="mt-1.5" />
    </div>
</div>

<div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="tahun" class="mc-label">Tahun <span class="text-red-400">*</span></label>
        <input id="tahun" name="tahun" type="number"
            class="mc-input"
            value="{{ old('tahun', $v->tahun ?? '') }}"
            min="1980" max="{{ date('Y') + 1 }}"
            placeholder="{{ date('Y') }}"
            required />
        <x-input-error :messages="$errors->get('tahun')" class="mt-1.5" />
    </div>
    <div>
        <label for="warna" class="mc-label">Warna <span class="text-mc-muted text-xs">(opsional)</span></label>
        <input id="warna" name="warna" type="text"
            class="mc-input"
            value="{{ old('warna', $v->warna ?? '') }}"
            placeholder="cth: Merah, Hitam" />
        <x-input-error :messages="$errors->get('warna')" class="mt-1.5" />
    </div>
</div>

<div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="tipe_mesin" class="mc-label">Tipe Mesin <span class="text-red-400">*</span></label>
        <select id="tipe_mesin" name="tipe_mesin" required class="mc-select">
            <option value="">-- Pilih tipe mesin --</option>
            @foreach (['2_tak' => '2 Tak', '4_tak' => '4 Tak', 'listrik' => 'Listrik'] as $val => $label)
                <option value="{{ $val }}" @selected(old('tipe_mesin', $v->tipe_mesin ?? '') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('tipe_mesin')" class="mt-1.5" />
    </div>
    <div>
        <label for="transmisi" class="mc-label">Transmisi <span class="text-red-400">*</span></label>
        <select id="transmisi" name="transmisi" required class="mc-select">
            <option value="">-- Pilih transmisi --</option>
            @foreach (['manual' => 'Manual', 'matic' => 'Matic'] as $val => $label)
                <option value="{{ $val }}" @selected(old('transmisi', $v->transmisi ?? '') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('transmisi')" class="mt-1.5" />
    </div>
</div>

<div class="mt-5">
    <label for="nomor_rangka" class="mc-label">Nomor Rangka <span class="text-mc-muted text-xs">(opsional)</span></label>
    <input id="nomor_rangka" name="nomor_rangka" type="text"
        class="mc-input font-mono"
        value="{{ old('nomor_rangka', $v->nomor_rangka ?? '') }}"
        placeholder="Nomor rangka kendaraan" />
    <x-input-error :messages="$errors->get('nomor_rangka')" class="mt-1.5" />
</div>

<div class="mt-5">
    <label for="catatan" class="mc-label">Catatan <span class="text-mc-muted text-xs">(opsional)</span></label>
    <textarea id="catatan" name="catatan" rows="3"
        class="mc-textarea"
        placeholder="Catatan tambahan tentang kondisi kendaraan...">{{ old('catatan', $v->catatan ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('catatan')" class="mt-1.5" />
</div>
