<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Delivery\AssignCourierRequest;
use App\Http\Requests\Delivery\CompleteDeliveryTaskRequest;
use App\Http\Resources\DeliveryTaskResource;
use App\Models\DeliveryTask;
use App\Models\User;
use App\Services\DeliveryTaskService;
use Illuminate\Http\JsonResponse;

class DeliveryTaskController extends Controller
{
    public function __construct(protected DeliveryTaskService $deliveryTaskService)
    {
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', DeliveryTask::class);

        return $this->success('Daftar delivery task', DeliveryTaskResource::collection(
            $this->deliveryTaskService->listFor(auth()->user())
        ));
    }

    public function show(DeliveryTask $deliveryTask): JsonResponse
    {
        $this->authorize('view', $deliveryTask);

        return $this->success('Detail delivery task', new DeliveryTaskResource($deliveryTask));
    }

    public function assign(AssignCourierRequest $request, DeliveryTask $deliveryTask): JsonResponse
    {
        $courier = User::findOrFail($request->validated('courier_id'));

        $updated = $this->deliveryTaskService->assignCourier($deliveryTask, $courier);

        return $this->success('Kurir berhasil ditugaskan', new DeliveryTaskResource($updated));
    }

    public function start(DeliveryTask $deliveryTask): JsonResponse
    {
        $this->authorize('complete', $deliveryTask);

        try {
            $updated = $this->deliveryTaskService->start($deliveryTask);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), null, 422);
        }

        return $this->success('Tugas dimulai', new DeliveryTaskResource($updated));
    }

    public function complete(CompleteDeliveryTaskRequest $request, DeliveryTask $deliveryTask): JsonResponse
    {
        try {
            $updated = $this->deliveryTaskService->complete($deliveryTask);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), null, 422);
        }

        return $this->success('Tugas selesai', new DeliveryTaskResource($updated));
    }
}
