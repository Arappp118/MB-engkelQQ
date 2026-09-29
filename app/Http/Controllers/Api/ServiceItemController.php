<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ServiceItem\StoreServiceItemRequest;
use App\Http\Requests\ServiceItem\UpdateServiceItemRequest;
use App\Http\Requests\ServiceItem\UpdateStockRequest;
use App\Http\Resources\ServiceItemResource;
use App\Models\ServiceItem;
use App\Services\ServiceItemService;
use Illuminate\Http\JsonResponse;

class ServiceItemController extends Controller
{
    public function __construct(protected ServiceItemService $serviceItemService)
    {
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', ServiceItem::class);

        $activeOnly = !auth()->user()->isAdmin();

        return $this->success('Daftar item', ServiceItemResource::collection(
            $this->serviceItemService->listFor(activeOnly: $activeOnly)
        ));
    }

    public function show(ServiceItem $serviceItem): JsonResponse
    {
        $this->authorize('view', $serviceItem);

        return $this->success('Detail item', new ServiceItemResource($serviceItem));
    }

    public function store(StoreServiceItemRequest $request): JsonResponse
    {
        $item = $this->serviceItemService->create($request->validated());

        return $this->success('Item berhasil dibuat', new ServiceItemResource($item), 201);
    }

    public function update(UpdateServiceItemRequest $request, ServiceItem $serviceItem): JsonResponse
    {
        $updated = $this->serviceItemService->update($serviceItem, $request->validated());

        return $this->success('Item berhasil diperbarui', new ServiceItemResource($updated));
    }

    public function updateStock(UpdateStockRequest $request, ServiceItem $serviceItem): JsonResponse
    {
        $updated = $this->serviceItemService->updateStock($serviceItem, $request->validated('stock'));

        return $this->success('Stok berhasil diperbarui', new ServiceItemResource($updated));
    }

    public function destroy(ServiceItem $serviceItem): JsonResponse
    {
        $this->authorize('delete', $serviceItem);

        $this->serviceItemService->deactivate($serviceItem);

        return $this->success('Item berhasil dinonaktifkan');
    }
}
