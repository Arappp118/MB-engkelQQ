<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceOrder\AddServiceOrderItemRequest;
use App\Http\Requests\ServiceOrder\SaveDiagnosisRequest;
use App\Models\ServiceOrder;
use App\Services\ServiceOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceOrderController extends Controller
{
    public function __construct(protected ServiceOrderService $serviceOrderService)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', ServiceOrder::class);

        $orders = $this->serviceOrderService->listFor(auth()->user());

        return view('service-orders.index', compact('orders'));
    }

    public function show(ServiceOrder $serviceOrder): View
    {
        $this->authorize('view', $serviceOrder);

        $serviceOrder->load(['booking.vehicle', 'booking.customer', 'mechanic', 'items']);

        return view('service-orders.show', compact('serviceOrder'));
    }

    public function start(ServiceOrder $serviceOrder): RedirectResponse
    {
        $this->authorize('update', $serviceOrder);

        try {
            $this->serviceOrderService->startService($serviceOrder);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('service-orders.show', $serviceOrder)
            ->with('success', 'Servis dimulai.');
    }

    public function diagnosis(SaveDiagnosisRequest $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $this->serviceOrderService->saveDiagnosis(
            $serviceOrder,
            $request->validated('diagnosis_mechanic'),
            $request->validated('notes')
        );

        return redirect()->route('service-orders.show', $serviceOrder)
            ->with('success', 'Diagnosis tersimpan.');
    }

    public function complete(ServiceOrder $serviceOrder): RedirectResponse
    {
        $this->authorize('complete', $serviceOrder);

        try {
            $this->serviceOrderService->completeService($serviceOrder);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('service-orders.show', $serviceOrder)
            ->with('success', 'Servis selesai.');
    }

    /**
     * Tambah item ke service order. Harga & nama SELALU diambil dari
     * database oleh ServiceOrderService::addItem() — request hanya
     * mengirim service_item_id dan quantity, tidak pernah harga.
     */
    public function addItem(AddServiceOrderItemRequest $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        try {
            $this->serviceOrderService->addItem(
                $serviceOrder,
                $request->validated('service_item_id'),
                $request->validated('quantity')
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('service-orders.show', $serviceOrder)
            ->with('success', 'Item berhasil ditambahkan.');
    }

}