<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight flex items-center gap-2">
                <span>Invoice</span>
                <span class="text-mc-muted font-normal text-base font-mono">#{{ $invoiceData['invoice_number'] }}</span>
            </h2>
            <a href="{{ route('service-orders.show', $serviceOrder) }}" class="text-sm text-mc-orange hover:text-mc-orange-hover flex items-center gap-1 transition-colors print:hidden">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 print:py-0 animate-fade-in">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 print:p-0 print:max-w-none">

            <div class="mc-card p-6 sm:p-8 border border-mc-border text-mc-text print:bg-white print:text-gray-900 print:shadow-none print:p-0 print:border-0 print:rounded-none">
                <div class="flex items-start justify-between mb-8 border-b border-mc-border print:border-gray-200 pb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-white print:text-gray-900 tracking-tight">INVOICE</h3>
                        <p class="text-sm text-mc-muted print:text-gray-500 mt-1 font-mono">{{ $invoiceData['invoice_number'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-white print:text-gray-900">Tanggal: {{ \Carbon\Carbon::parse($invoiceData['tanggal'])->format('d/m/Y') }}</p>
                        <p class="text-sm text-mc-muted print:text-gray-500">No. Booking: <span class="font-mono text-mc-orange print:text-gray-900">{{ $invoiceData['nomor_booking'] }}</span></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h4 class="text-xs font-semibold text-mc-muted print:text-gray-500 uppercase tracking-wider mb-2">Informasi Pelanggan</h4>
                        <p class="text-sm font-medium text-white print:text-gray-900">{{ $invoiceData['customer']->name }}</p>
                        <p class="text-sm text-mc-muted print:text-gray-600">{{ $invoiceData['customer']->email }}</p>
                    </div>
                    <div class="sm:text-right">
                        <h4 class="text-xs font-semibold text-mc-muted print:text-gray-500 uppercase tracking-wider mb-2">Kendaraan</h4>
                        <p class="text-sm font-medium text-white print:text-gray-900">{{ $invoiceData['vehicle']->merk }} {{ $invoiceData['vehicle']->model }}</p>
                        <p class="text-sm text-mc-muted print:text-gray-600 font-mono">{{ $invoiceData['vehicle']->nomor_polisi }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto mt-6">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-mc-muted print:text-gray-500 border-b border-mc-border print:border-gray-200 bg-mc-sidebar print:bg-gray-50">
                            <tr>
                                <th class="py-3 px-4 font-semibold rounded-tl-md">Item / Deskripsi</th>
                                <th class="py-3 px-4 text-right font-semibold">Harga Satuan</th>
                                <th class="py-3 px-4 text-center font-semibold">Qty</th>
                                <th class="py-3 px-4 text-right font-semibold rounded-tr-md">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-mc-border/60 print:divide-gray-100">
                            @foreach ($invoiceData['items'] as $item)
                                <tr>
                                    <td class="py-3 px-4 font-medium text-white print:text-gray-800">
                                        {{ $item->item_name_snapshot }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-mc-muted print:text-gray-600">
                                        Rp{{ number_format($item->price_snapshot, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-center text-mc-muted print:text-gray-600">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium text-white print:text-gray-800">
                                        Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-mc-border print:border-gray-200">
                            <tr>
                                <td colspan="3" class="pt-4 px-4 text-right text-sm text-mc-muted print:text-gray-500">Subtotal Servis</td>
                                <td class="pt-4 px-4 text-right font-semibold text-white print:text-gray-800">
                                    Rp{{ number_format($invoiceData['subtotal'], 0, ',', '.') }}
                                </td>
                            </tr>
                            @if ($invoiceData['delivery_fee'])
                                <tr>
                                    <td colspan="3" class="pt-2 px-4 text-right text-sm text-mc-muted print:text-gray-500">Biaya Antar-Jemput</td>
                                    <td class="pt-2 px-4 text-right font-semibold text-white print:text-gray-800">
                                        Rp{{ number_format($invoiceData['delivery_fee'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="pt-3 px-4 text-right font-bold text-white print:text-gray-900 uppercase">Grand Total</td>
                                <td class="pt-3 px-4 text-right font-bold text-lg text-mc-orange print:text-indigo-700">
                                    Rp{{ number_format($invoiceData['grand_total'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-10 border-t border-mc-border print:border-gray-200 pt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-xs font-semibold text-mc-muted print:text-gray-500 uppercase tracking-wider mb-2">Status Pembayaran</h4>
                        @if ($invoiceData['payment'] && $invoiceData['payment']->status === 'verified')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold badge-green">
                                LUNAS
                            </span>
                        @elseif ($invoiceData['payment'] && $invoiceData['payment']->status === 'pending')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold badge-yellow">
                                MENUNGGU VERIFIKASI
                            </span>
                        @elseif ($invoiceData['payment'] && $invoiceData['payment']->status === 'failed')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold badge-red">
                                GAGAL / DITOLAK
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold badge-gray">
                                BELUM DIBAYAR
                            </span>
                        @endif

                        @if ($invoiceData['payment'])
                            <p class="text-sm text-mc-muted print:text-gray-600 mt-2">Metode:
                                <span class="font-medium text-white print:text-gray-900">
                                    {{ match($invoiceData['payment']->payment_method) {
                                        'cash' => 'Tunai (Cash)',
                                        'transfer' => 'Transfer Bank',
                                        'qris' => 'QRIS',
                                        default => ucfirst($invoiceData['payment']->payment_method),
                                    } }}
                                </span>
                            </p>
                        @endif
                    </div>
                    
                    <div class="text-right flex justify-end items-end">
                        <button onclick="window.print()" class="btn-secondary text-xs px-4 py-2 inline-flex items-center gap-2 print:hidden">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Cetak / PDF
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
