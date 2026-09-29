<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Mekanik</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Total Ditugaskan</p><p class="text-2xl font-bold">{{ $assigned_total }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Pending</p><p class="text-2xl font-bold">{{ $pending }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Sedang Dikerjakan</p><p class="text-2xl font-bold">{{ $in_progress }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Selesai</p><p class="text-2xl font-bold">{{ $completed }}</p></div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-semibold mb-4">Pekerjaan Aktif</h3>
                @forelse ($active_orders as $order)
                    <div class="border-b py-2 text-sm">
                        {{ $order->booking->vehicle->nomor_polisi ?? '-' }} — {{ $order->status }}
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Tidak ada pekerjaan aktif.</p>
                @endforelse
            </div>

            <p class="text-sm">Notifikasi belum dibaca: <strong>{{ $unread_notifications }}</strong></p>
        </div>
    </div>
</x-app-layout>
