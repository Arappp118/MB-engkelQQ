<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\RejectPaymentRequest;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    /**
     * Daftar pembayaran — hanya admin yang boleh mengakses.
     * Filter by status via query param ?status=waiting_verification|verified|rejected|all
     */
    public function index(): View
    {
        $this->authorize('verify', Payment::class);

        $status = request('status', 'waiting_verification');

        $payments = Payment::with([
                'serviceOrder.booking.customer',
                'serviceOrder.booking.vehicle',
            ])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $pendingCount = Payment::where('status', 'waiting_verification')->count();

        return view('payments.index', compact('payments', 'status', 'pendingCount'));
    }

    /**
     * Buat payment untuk service order tertentu. Amount SELALU dari
     * $order->grand_total (server), tidak pernah dari request.
     */
    public function store(StorePaymentRequest $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $this->paymentService->create(
            $serviceOrder,
            $request->validated(),
            $request->file('proof')
        );

        return redirect()->route('service-orders.show', $serviceOrder)
            ->with('success', 'Pembayaran berhasil diajukan, menunggu verifikasi admin.');
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        return view('payments.show', compact('payment'));
    }

    public function verify(Payment $payment): RedirectResponse
    {
        $this->authorize('verify', Payment::class);

        try {
            $this->paymentService->verify($payment, auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(RejectPaymentRequest $request, Payment $payment): RedirectResponse
    {
        try {
            $this->paymentService->reject($payment, auth()->id(), $request->validated('reason'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Pembayaran ditolak.');
    }
}
