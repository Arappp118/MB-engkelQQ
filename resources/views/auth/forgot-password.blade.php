<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-white">Lupa Password?</h2>
            <p class="text-mc-muted text-sm mt-1">
                Masukkan email Anda dan kami akan mengirimkan link reset password.
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" type="email" name="email" :value="old('email')"
                    placeholder="email@contoh.com" required autofocus />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <button type="submit" class="btn-primary w-full py-3 text-base">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Kirim Link Reset
            </button>
        </form>

        <p class="text-center text-sm text-mc-muted">
            <a href="{{ route('login') }}" class="text-mc-orange hover:text-orange-400 font-medium transition-colors">
                &larr; Kembali ke Login
            </a>
        </p>
    </div>
</x-guest-layout>
