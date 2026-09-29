<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Kurir</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Task Pickup</p><p class="text-2xl font-bold">{{ $pickup_total }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Task Delivery</p><p class="text-2xl font-bold">{{ $delivery_total }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Task Aktif</p><p class="text-2xl font-bold">{{ $active_total }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Selesai</p><p class="text-2xl font-bold">{{ $completed_total }}</p></div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-semibold mb-4">Task Aktif</h3>
                @forelse ($active_tasks as $task)
                    <div class="border-b py-2 text-sm">
                        {{ ucfirst($task->type) }} — {{ $task->booking->vehicle->nomor_polisi ?? '-' }}
                        — Rp{{ number_format($task->delivery_fee, 0, ',', '.') }} — {{ $task->status }}
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Tidak ada task aktif.</p>
                @endforelse
            </div>

            <p class="text-sm">Notifikasi belum dibaca: <strong>{{ $unread_notifications }}</strong></p>
        </div>
    </div>
</x-app-layout>
