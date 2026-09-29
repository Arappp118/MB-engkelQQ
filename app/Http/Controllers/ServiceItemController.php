<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceItem\StoreServiceItemRequest;
use App\Http\Requests\ServiceItem\UpdateServiceItemRequest;
use App\Http\Requests\ServiceItem\UpdateStockRequest;
use App\Models\ServiceItem;
use App\Services\ServiceItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceItemController extends Controller
{
    public function __construct(protected ServiceItemService $serviceItemService)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', ServiceItem::class);

        // Admin lihat semua (termasuk nonaktif) untuk keperluan kelola master data.
        // Role lain hanya lihat item aktif.
        $activeOnly = !auth()->user()->isAdmin();

        $items = $this->serviceItemService->listFor(activeOnly: $activeOnly);

        return view('service-items.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorize('create', ServiceItem::class);

        return view('service-items.create');
    }

    public function store(StoreServiceItemRequest $request): RedirectResponse
    {
        $item = $this->serviceItemService->create($request->validated());

        return redirect()->route('service-items.show', $item)
            ->with('success', 'Item berhasil ditambahkan.');
    }

    public function show(ServiceItem $serviceItem): View
    {
        $this->authorize('view', $serviceItem);

        return view('service-items.show', compact('serviceItem'));
    }

    public function edit(ServiceItem $serviceItem): View
    {
        $this->authorize('update', $serviceItem);

        return view('service-items.edit', compact('serviceItem'));
    }

    public function update(UpdateServiceItemRequest $request, ServiceItem $serviceItem): RedirectResponse
    {
        $this->serviceItemService->update($serviceItem, $request->validated());

        return redirect()->route('service-items.show', $serviceItem)
            ->with('success', 'Item berhasil diperbarui.');
    }

    public function updateStock(UpdateStockRequest $request, ServiceItem $serviceItem): RedirectResponse
    {
        $this->serviceItemService->updateStock($serviceItem, $request->validated('stock'));

        return redirect()->route('service-items.show', $serviceItem)
            ->with('success', 'Stok berhasil diperbarui.');
    }

    public function destroy(ServiceItem $serviceItem): RedirectResponse
    {
        $this->authorize('delete', $serviceItem);

        $this->serviceItemService->deactivate($serviceItem);

        return redirect()->route('service-items.index')
            ->with('success', 'Item berhasil dinonaktifkan.');
    }
}
