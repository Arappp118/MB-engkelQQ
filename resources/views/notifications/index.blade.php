<x-app-layout>
    <x-slot name="header">
        Notifikasi
    </x-slot>

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-mc-text">
                    Notifikasi
                </h1>

                <p class="mt-1 text-sm text-mc-muted">
                    Informasi terbaru mengenai booking, servis, pembayaran, dan aktivitas MotoCare.
                </p>
            </div>

            @php
                $unreadCount = $notifications->getCollection()
                    ->whereNull('read_at')
                    ->count();
            @endphp

            @if ($unreadCount > 0)
                <div class="inline-flex items-center gap-2 rounded-full bg-mc-orange/10 px-4 py-2 text-sm font-medium text-mc-orange">
                    <span class="h-2 w-2 rounded-full bg-mc-orange"></span>
                    {{ $unreadCount }} belum dibaca
                </div>
            @endif
        </div>

        {{-- Notification list --}}
        <div class="space-y-3">

            @forelse ($notifications as $notification)

                <div
                    class="mc-card p-4 transition-all duration-200 hover:border-mc-orange/40 sm:p-5
                    {{ is_null($notification->read_at) ? 'border-mc-orange/30' : '' }}"
                >

                    <div class="flex gap-4">

                        {{-- Icon --}}
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl
                            {{ is_null($notification->read_at)
                                ? 'bg-mc-orange/10 text-mc-orange'
                                : 'bg-mc-border text-mc-muted' }}">

                            @if ($notification->type === 'booking')
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
                                </svg>

                            @elseif ($notification->type === 'payment')
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V5m0 11v3m7-7a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>

                            @elseif ($notification->type === 'service')
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 4a1 1 0 012 0v1a1 1 0 002 .732l.707-.707a1 1 0 011.414 1.414l-.707.707A1 1 0 0017.146 9H18a1 1 0 110 2h-1a1 1 0 00-.732 2.146l.707.707a1 1 0 01-1.414 1.414l-.707-.707A1 1 0 0013 15.292V16a1 1 0 11-2 0v-.708a1 1 0 00-1.854-.732l-.707.707a1 1 0 01-1.414-1.414l.707-.707A1 1 0 007.854 11H7a1 1 0 110-2h.854a1 1 0 00.732-1.854l-.707-.707a1 1 0 011.414-1.414l.707.707A1 1 0 0011 5V4z" />
                                </svg>

                            @else
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            @endif

                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1">

                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">

                                <div>
                                    <div class="flex items-center gap-2">

                                        <h2 class="font-semibold text-mc-text">
                                            {{ $notification->title }}
                                        </h2>

                                        @if (is_null($notification->read_at))
                                            <span class="h-2 w-2 rounded-full bg-mc-orange"></span>
                                        @endif

                                    </div>

                                    <p class="mt-1 text-sm leading-6 text-mc-muted">
                                        {{ $notification->message }}
                                    </p>
                                </div>

                                <span class="shrink-0 text-xs text-mc-muted">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </span>

                            </div>

                            {{-- Metadata --}}
                            @if ($notification->entity_type)
                                <div class="mt-3">
                                    <span class="inline-flex rounded-full bg-mc-border px-3 py-1 text-xs text-mc-muted">
                                        {{ $notification->entity_type }}
                                        @if ($notification->entity_id)
                                            #{{ $notification->entity_id }}
                                        @endif
                                    </span>
                                </div>
                            @endif

                            {{-- Mark as read --}}
                            @if (is_null($notification->read_at))
                                <div class="mt-4">

                                    <form
                                        method="POST"
                                        action="{{ route('notifications.read', $notification) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="text-sm font-medium text-mc-orange transition-colors hover:text-white"
                                        >
                                            Tandai sudah dibaca
                                        </button>
                                    </form>

                                </div>
                            @else
                                <div class="mt-4 text-xs text-mc-muted">
                                    Sudah dibaca
                                    @if ($notification->read_at)
                                        · {{ $notification->read_at->diffForHumans() }}
                                    @endif
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

            @empty

                {{-- Empty state --}}
                <div class="mc-card flex flex-col items-center justify-center px-6 py-16 text-center">

                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-mc-border text-mc-muted">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-8 w-8"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>

                    </div>

                    <h2 class="mt-5 text-lg font-semibold text-mc-text">
                        Belum ada notifikasi
                    </h2>

                    <p class="mt-2 max-w-md text-sm text-mc-muted">
                        Notifikasi mengenai booking, servis, pembayaran, dan aktivitas lainnya akan muncul di sini.
                    </p>

                </div>

            @endforelse

        </div>

        {{-- Pagination --}}
        @if ($notifications->hasPages())
            <div class="mc-card p-4">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</x-app-layout>