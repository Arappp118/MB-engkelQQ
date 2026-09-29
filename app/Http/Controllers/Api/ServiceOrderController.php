<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ServiceOrder\SaveDiagnosisRequest;
use App\Http\Resources\ServiceOrderResource;
use App\Models\ServiceOrder;
use App\Services\ServiceOrderService;
use Illuminate\Http\JsonResponse;

class ServiceOrderController extends Controller
{
    public function __construct(protected ServiceOrderService $serviceOrderService)
    {
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', ServiceOrder::class);

        return $this->success('Daftar service order', ServiceOrderResource::collection(
            $this->serviceOrderService->listFor(auth()->user())
        ));
    }

    public function show(ServiceOrder $serviceOrder): JsonResponse
    {
        $this->authorize('view', $serviceOrder);

        return $this->success('Detail service order', new ServiceOrderResource($serviceOrder->load('items')));
    }

    public function start(ServiceOrder $serviceOrder): JsonResponse
    {
        $this->authorize('update', $serviceOrder);

        try {
            $this->serviceOrderService->startService($serviceOrder);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), null, 422);
        }

        return $this->success('Servis dimulai', new ServiceOrderResource($serviceOrder->fresh()));
    }

    /**
     * Reuse SaveDiagnosisRequest yang sama dengan Web — otorisasi (hanya
     * mekanik pemilik/admin) sudah di dalamnya, tidak duplikasi logic.
     */
    public function diagnosis(SaveDiagnosisRequest $request, ServiceOrder $serviceOrder): JsonResponse
    {
        $this->serviceOrderService->saveDiagnosis(
            $serviceOrder,
            $request->validated('diagnosis_mechanic'),
            $request->validated('notes')
        );

        return $this->success('Diagnosis tersimpan', new ServiceOrderResource($serviceOrder->fresh()));
    }

    public function complete(ServiceOrder $serviceOrder): JsonResponse
    {
        $this->authorize('complete', $serviceOrder);

        try {
            $this->serviceOrderService->completeService($serviceOrder);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), null, 422);
        }

        return $this->success('Servis selesai', new ServiceOrderResource($serviceOrder->fresh()));
    }
}
