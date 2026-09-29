<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Admin</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Customer</p><p class="text-2xl font-bold">{{ $total_customers }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Kendaraan</p><p class="text-2xl font-bold">{{ $total_vehicles }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Booking</p><p class="text-2xl font-bold">{{ $total_bookings }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Booking Aktif</p><p class="text-2xl font-bold">{{ $active_bookings }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Service Order</p><p class="text-2xl font-bold">{{ $total_service_orders }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Payment Pending</p><p class="text-2xl font-bold">{{ $payment_pending }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Payment Verified</p><p class="text-2xl font-bold">{{ $payment_verified }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Delivery Task</p><p class="text-2xl font-bold">{{ $delivery_task_total }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Invoice</p><p class="text-2xl font-bold">{{ $invoice_total }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Mekanik</p><p class="text-2xl font-bold">{{ $mechanic_total }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Kurir</p><p class="text-2xl font-bold">{{ $courier_total }}</p></div>
                <div class="bg-white shadow-sm rounded-lg p-6"><p class="text-sm text-gray-500">Item/Sparepart</p><p class="text-2xl font-bold">{{ $service_item_total }}</p></div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-semibold mb-4">Booking Terbaru</h3>
                @forelse ($recent_bookings as $booking)
                    <div class="border-b py-2 text-sm">
                        {{ $booking->nomor_booking }} — {{ $booking->customer->name ?? '-' }} — {{ $booking->status }}
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Belum ada booking.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
