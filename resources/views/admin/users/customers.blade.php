<x-app-layout>
    <x-slot name="header">Manajemen Pelanggan</x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-mc-card via-mc-sidebar to-mc-card border border-mc-border p-6 shadow-xl">
            <div class="absolute -right-12 -top-12 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/15 border border-blue-500/30 text-blue-400 text-xs font-semibold mb-3">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Read-only
                    </div>
                    <h1 class="text-2xl font-extrabold text-white">Daftar Pelanggan</h1>
                    <p class="text-sm text-mc-muted mt-1">{{ $customers->total() }} pelanggan terdaftar</p>
                </div>
            </div>
        </div>

        {{-- Flash message --}}
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
                            <th class="text-left px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">No. HP</th>
                            <th class="text-left px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">Status</th>
                            <th class="text-left px-5 py-3 text-mc-muted font-semibold text-xs uppercase tracking-wider">Daftar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-mc-border/50">
                        @forelse ($customers as $customer)
                            <tr class="hover:bg-mc-sidebar/30 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                                        </div>
                                        <span class="font-medium text-white">{{ $customer->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-mc-text">{{ $customer->email }}</td>
                                <td class="px-5 py-3.5 text-mc-text">{{ $customer->phone ?? '—' }}</td>
                                <td class="px-5 py-3.5">
                                    @if ($customer->is_active)
                                        <span class="badge badge-green">Aktif</span>
                                    @else
                                        <span class="badge badge-red">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-mc-muted text-xs">{{ $customer->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-mc-muted">
                                    Belum ada pelanggan terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($customers->hasPages())
                <div class="px-5 py-4 border-t border-mc-border">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
