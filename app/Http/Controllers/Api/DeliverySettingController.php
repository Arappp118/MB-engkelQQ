<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Delivery\UpdateDeliverySettingRequest;
use App\Models\DeliverySetting;
use App\Services\DeliveryTaskService;
use Illuminate\Http\JsonResponse;

class DeliverySettingController extends Controller
{
    public function __construct(protected DeliveryTaskService $deliveryTaskService)
    {
    }

    public function show(): JsonResponse
    {
        return $this->success('Tarif ongkir', [
            'price_per_km' => (float) DeliverySetting::get('price_per_km', 3000),
        ]);
    }

    public function update(UpdateDeliverySettingRequest $request): JsonResponse
    {
        $this->deliveryTaskService->updateTariff((float) $request->validated('price_per_km'));

        return $this->success('Tarif berhasil diperbarui');
    }
}
