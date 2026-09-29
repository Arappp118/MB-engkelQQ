<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Service Order</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="bg-green-100 border border-green-300 text-green-800 rounded-md p-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-300 text-red-800 rounded-md p-4 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th scope="col" class="p-4">No. Booking</th>
                            <th scope="col" class="p-4">Kendaraan</th>
                            @if (auth()->user()->isAdmin() || auth()->user()->isCustomer())
                                <th scope="col" class="p-4">Mekanik</th>
                            @endif
                            @if (auth()->user()->isAdmin())
                                <th scope="col" class="p-4">Customer</th>
                            @endif
                            <th scope="col" class="p-4">Status</th>
                            <th scope="col" class="p-4">Total</th>
                            <th scope="col" class="p-4"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 font-medium text-gray-900">
                                    {{ $order->booking->nomor_booking ?? '-' }}
                                </td>
                                <td class="p-4 text-gray-700">
                                    @if ($order->booking?->vehicle)
                                        <span class="font-medium">{{ $order->booking->vehicle->merk }}
                                            {{ $order->booking->vehicle->model }}</span>
                                        <br>
                                        <span class="text-xs text-gray-500">
                                            {{ $order->booking->vehicle->nomor_polisi }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                @if (auth()->user()->isAdmin() || auth()->user()->isCustomer())
                                    <td class="p-4 text-gray-600">
                                        {{ $order->mechanic?->name ?? '-' }}
                                    </td>
                                @endif
                                @if (auth()->user()->isAdmin())
                                    <td class="p-4 text-gray-600">
                                        {{ $order->booking?->customer?->name ?? '-' }}
                                    </td>
                                @endif
                                <td class="p-4">
                                    @php
                                        $statusColor = match($order->status) {
                                            'pending'    => 'bg-yellow-100 text-yellow-800',
                                            'in_progress'=> 'bg-blue-100 text-blue-800',
                                            'completed'  => 'bg-emerald-100 text-emerald-800',
                                            default      => 'bg-gray-100 text-gray-700',
                                        };
                                        $statusLabel = match($order->status) {
                                            'pending'    => 'Menunggu',
                                            'in_progress'=> 'Sedang Dikerjakan',
                                            'completed'  => 'Selesai',
                                            default      => ucfirst($order->status),
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="p-4 text-gray-700">
                                    @if ($order->grand_total)
                                        Rp{{ number_format($order->grand_total, 0, ',', '.') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('service-orders.show', $order) }}"
                                        class="text-indigo-600 hover:underline font-medium">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-10 text-center text-gray-400">
                                    <p class="text-base font-medium">Belum ada service order.</p>
                                    <p class="text-sm mt-1">
                                        @if (auth()->user()->isCustomer())
                                            <a href="{{ route('bookings.create') }}"
                                                class="text-indigo-600 hover:underline">Buat booking baru</a>
                                            untuk memulai servis kendaraan Anda.
                                        @else
                                            Tidak ada data saat ini.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
