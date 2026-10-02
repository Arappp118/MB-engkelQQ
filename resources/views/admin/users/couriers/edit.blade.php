<x-app-layout>
    <x-slot name="header">Edit Kurir</x-slot>

    <div class="max-w-2xl mx-auto space-y-6 animate-fade-in">

        {{-- Header --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.couriers') }}"
               class="p-2 rounded-lg text-mc-muted hover:text-white hover:bg-mc-card border border-transparent hover:border-mc-border transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-white">Edit Kurir</h1>
                <p class="text-sm text-mc-muted">{{ $user->name }}</p>
            </div>
        </div>

        {{-- Status badge --}}
        @if (! $user->is_active)
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Akun ini sedang <strong>nonaktif</strong>. Kurir tidak dapat login.
            </div>
        @endif

        {{-- Form --}}
        <div class="mc-card space-y-5">
            <form method="POST" action="{{ route('admin.users.couriers.update', $user) }}" id="form-edit-courier">
                @csrf @method('PATCH')

                {{-- Nama --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-mc-text mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                           class="w-full px-4 py-2.5 rounded-xl bg-mc-sidebar border {{ $errors->has('name') ? 'border-red-500/60' : 'border-mc-border' }} text-white placeholder-mc-muted focus:outline-none focus:ring-2 focus:ring-mc-orange/40 focus:border-mc-orange transition-all"
                           placeholder="Nama lengkap kurir" autofocus>
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-mc-text mb-1.5">Email <span class="text-red-400">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                           class="w-full px-4 py-2.5 rounded-xl bg-mc-sidebar border {{ $errors->has('email') ? 'border-red-500/60' : 'border-mc-border' }} text-white placeholder-mc-muted focus:outline-none focus:ring-2 focus:ring-mc-orange/40 focus:border-mc-orange transition-all">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password (opsional) --}}
                <div class="p-4 rounded-xl bg-mc-sidebar/50 border border-mc-border/60 space-y-4">
                    <p class="text-xs text-mc-muted font-medium uppercase tracking-wider">Ganti Password <span class="normal-case text-mc-muted/60">(kosongkan jika tidak ingin mengubah)</span></p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-mc-text mb-1.5">Password Baru</label>
                            <input id="password" name="password" type="password"
                                   class="w-full px-4 py-2.5 rounded-xl bg-mc-sidebar border {{ $errors->has('password') ? 'border-red-500/60' : 'border-mc-border' }} text-white placeholder-mc-muted focus:outline-none focus:ring-2 focus:ring-mc-orange/40 focus:border-mc-orange transition-all"
                                   placeholder="Min. 8 karakter">
                            @error('password')
                                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-mc-text mb-1.5">Konfirmasi</label>
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                   class="w-full px-4 py-2.5 rounded-xl bg-mc-sidebar border border-mc-border text-white placeholder-mc-muted focus:outline-none focus:ring-2 focus:ring-mc-orange/40 focus:border-mc-orange transition-all"
                                   placeholder="Ulangi password">
                        </div>
                    </div>
                </div>

                {{-- No HP --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-mc-text mb-1.5">No. Telepon</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                           class="w-full px-4 py-2.5 rounded-xl bg-mc-sidebar border border-mc-border text-white placeholder-mc-muted focus:outline-none focus:ring-2 focus:ring-mc-orange/40 focus:border-mc-orange transition-all"
                           placeholder="08xxxxxxxxxx">
                </div>

                {{-- Alamat --}}
                <div>
                    <label for="address" class="block text-sm font-medium text-mc-text mb-1.5">Alamat</label>
                    <textarea id="address" name="address" rows="3"
                              class="w-full px-4 py-2.5 rounded-xl bg-mc-sidebar border border-mc-border text-white placeholder-mc-muted focus:outline-none focus:ring-2 focus:ring-mc-orange/40 focus:border-mc-orange transition-all resize-none"
                              placeholder="Alamat lengkap (opsional)">{{ old('address', $user->address) }}</textarea>
                </div>

                {{-- Note --}}
                <p class="text-xs text-mc-muted/70 italic">
                    <svg class="w-3.5 h-3.5 inline mr-1 text-mc-orange/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Role <strong class="text-cyan-400">Kurir</strong> tidak dapat diubah melalui form ini.
                </p>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-mc-border">
                    <a href="{{ route('admin.users.couriers') }}"
                       class="px-4 py-2 rounded-xl text-sm text-mc-muted hover:text-white hover:bg-mc-sidebar border border-mc-border transition-all">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-mc-orange hover:bg-mc-orange-hover text-white text-sm font-semibold transition-all shadow-lg shadow-mc-orange/25">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
