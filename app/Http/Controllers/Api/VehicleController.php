<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreVehicleApiRequest;
use App\Http\Requests\Api\UpdateVehicleApiRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Http\JsonResponse;

class VehicleController extends Controller
{
    public function __construct(protected VehicleService $vehicleService)
    {
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Vehicle::class);

        $vehicles = $this->vehicleService->listFor(auth()->user());

        return $this->success('Daftar kendaraan', VehicleResource::collection($vehicles));
    }

    /**
     * user_id SELALU dari auth()->user(), tidak pernah dari request body
     * (lihat StoreVehicleApiRequest — field itu bahkan tidak ada di rules).
     */
    public function store(StoreVehicleApiRequest $request): JsonResponse
    {
        $vehicle = $this->vehicleService->create($request->validated(), $request->user());

        return $this->success('Kendaraan berhasil ditambahkan', new VehicleResource($vehicle), 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('view', $vehicle);

        return $this->success('Detail kendaraan', new VehicleResource($vehicle));
    }

    public function update(UpdateVehicleApiRequest $request, Vehicle $vehicle): JsonResponse
    {
        $updated = $this->vehicleService->update($vehicle, $request->validated());

        return $this->success('Kendaraan berhasil diperbarui', new VehicleResource($updated));
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('delete', $vehicle);

        try {
            $this->vehicleService->delete($vehicle);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), null, 422);
        }

        return $this->success('Kendaraan berhasil dihapus');
    }
}
