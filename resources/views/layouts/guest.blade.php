<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MotoCare') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-mc-bg text-mc-text">

    <div class="min-h-screen flex">

        <!-- Left Branding Panel (hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-[55%] relative overflow-hidden bg-gradient-to-br from-mc-sidebar via-[#1a1f2e] to-mc-bg flex-col justify-between p-12">

            <!-- Background decoration -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-32 -left-32 w-96 h-96 bg-mc-orange/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-80 h-80 bg-mc-orange/5 rounded-full blur-3xl"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-px h-full bg-gradient-to-b from-transparent via-mc-orange/20 to-transparent"></div>
            </div>

            <!-- Logo -->
            <div class="relative z-10">
                <a href="/" class="inline-flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-mc-orange to-orange-700 flex items-center justify-center shadow-lg shadow-mc-orange/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-white font-bold text-2xl tracking-tight">MotoCare</span>
                        <p class="text-mc-muted text-xs">Bengkel Motor Profesional</p>
                    </div>
                </a>
            </div>

            <!-- Hero Content -->
            <div class="relative z-10 space-y-8">
                <div class="space-y-4">
                    <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight">
                        Servis Motor<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-mc-orange to-orange-400">Lebih Mudah</span>
                    </h1>
                    <p class="text-gray-400 text-lg leading-relaxed max-w-sm">
                        Booking servis motor kapan saja, pantau progress secara real-time, dan bayar dengan mudah.
                    </p>
                </div>

                <!-- Feature list -->
                <div class="space-y-4">
                    @foreach ([
                        ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'text' => 'Booking mudah & cepat'],
                        ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Tracking status real-time'],
                        ['icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'text' => 'Pembayaran aman & transparan'],
                    ] as $feature)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-mc-orange/15 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-mc-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/>
                                </svg>
                            </div>
                            <span class="text-gray-300 text-sm">{{ $feature['text'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Bottom -->
            <div class="relative z-10">
                <p class="text-mc-muted text-xs">&copy; {{ date('Y') }} MotoCare. All rights reserved.</p>
            </div>
        </div>

        <!-- Right Auth Panel -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 sm:p-10">
            <!-- Mobile logo -->
            <div class="lg:hidden mb-8 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-mc-orange to-orange-700 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-xl">MotoCare</span>
            </div>

            <!-- Form Container -->
            <div class="w-full max-w-md animate-fade-in">
                {{ $slot }}
            </div>
        </div>
    </div>

</body>
</html>
