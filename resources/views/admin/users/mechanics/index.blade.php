<x-app-layout>
    <x-slot name="header">Manajemen Mekanik</x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-mc-card via-mc-sidebar to-mc-card border border-mc-border p-6 shadow-xl">
            <div class="absolute -right-12 -top-12 w-64 h-64 bg-mc-orange/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-mc-orange/15 border border-mc-orange/30 text-mc-orange text-xs font-semibold mb-3">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Staff
                    </div>
                    <h1 class="text-2xl font-extrabold text-white">Daftar Mekanik</h1>
                    <p class="text-sm text-mc-muted mt-1">{{ $mechanics->total() }} mekanik terdaftar</p>
                </div>
                <a href="{{ route('admin.users.mechanics.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-mc-orange hover:bg-mc-orange-hover text-white text-sm font-semibold transition-all shadow-lg shadow-mc-orange/25 hover:shadow-mc-orange/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Mekanik
                </a>
            </div>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Table --}}
        <div class="mc-card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-mc-border bg-mc-sidebar/60">
                            <th class="text-left px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">Nama</th>
                            <th class="text-left px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">Email</th>
                            <th class="text-left px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">Spesialisasi</th>
                            <th class="text-left px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">No. HP</th>
                            <th class="text-left px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">Status</th>
                            <th class="text-right px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-mc-border/50">
                        @forelse ($mechanics as $mechanic)
                            <tr class="hover:bg-mc-sidebar/30 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-mc-orange to-orange-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($mechanic->name, 0, 1)) }}
                                        </div>
                                        <span class="font-medium text-white">{{ $mechanic->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-mc-text">{{ $mechanic->email }}</td>
                                <td class="px-5 py-3.5">
                                    @php
                                        $specLabel = $specializations[$mechanic->specialization] ?? $mechanic->specialization ?? '—';
                                        $specClass = match($mechanic->specialization) {
                                            'mekanik_2_tak'       => 'badge-blue',
                                            'mekanik_4_tak'       => 'badge-orange',
                                            'mekanik_kelistrikan' => 'badge-yellow',
                                            default               => 'badge-gray',
                                        };
                                    @endphp
                                    @if ($mechanic->specialization)
                                        <span class="badge {{ $specClass }}">{{ $specLabel }}</span>
                                    @else
                                        <span class="text-mc-muted">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-mc-text">{{ $mechanic->phone ?? '—' }}</td>
                                <td class="px-5 py-3.5">
                                    @if ($mechanic->is_active)
                                        <span class="badge badge-green">Aktif</span>
                                    @else
                                        <span class="badge badge-red">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.mechanics.edit', $mechanic) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-mc-card border border-mc-border text-mc-text hover:text-white hover:border-mc-orange/50 text-xs font-medium transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.users.mechanics.toggle', $mechanic) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-medium transition-all
                                                    {{ $mechanic->is_active
                                                        ? 'bg-red-500/10 border-red-500/30 text-red-400 hover:bg-red-500/20'
                                                        : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20' }}"
                                                onclick="return confirm('{{ $mechanic->is_active ? 'Nonaktifkan' : 'Aktifkan' }} mekanik ini?')">
                                                @if ($mechanic->is_active)
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                    Nonaktifkan
                                                @else
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Aktifkan
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-mc-muted">
                                    Belum ada mekanik. <a href="{{ route('admin.users.mechanics.create') }}" class="text-mc-orange hover:underline">Tambah sekarang</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($mechanics->hasPages())
                <div class="px-5 py-4 border-t border-mc-border">
                    {{ $mechanics->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
