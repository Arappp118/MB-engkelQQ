<x-app-layout>
    <x-slot name="header">Tambah Kendaraan</x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Page Header --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('vehicles.index') }}"
               class="w-9 h-9 flex items-center justify-center rounded-lg border border-mc-border hover:bg-mc-card transition-colors text-mc-muted hover:text-mc-text flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-mc-text">Tambah Kendaraan</h1>
                <p class="text-sm text-mc-muted mt-0.5">Daftarkan kendaraan baru</p>
            </div>
        </div>

        <div class="max-w-2xl">
            <div class="mc-card">
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

                <form action="{{ route('vehicles.store') }}" method="POST">
                    @csrf
                    @include('vehicles._form')

                    <div class="mt-7 flex items-center gap-3 pt-5 border-t border-mc-border">
                        <button type="submit" class="btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Kendaraan
                        </button>
                        <a href="{{ route('vehicles.index') }}" class="btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
