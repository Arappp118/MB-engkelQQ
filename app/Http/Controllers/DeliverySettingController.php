<?php

namespace App\Http\Controllers;

use App\Http\Requests\Delivery\UpdateDeliverySettingRequest;
use App\Models\DeliverySetting;
use App\Services\DeliveryTaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeliverySettingController extends Controller
{
    public function __construct(protected DeliveryTaskService $deliveryTaskService)
    {
    }

    /**
     * Semua role login boleh lihat tarif (dibutuhkan untuk transparansi
     * biaya), tapi hanya admin yang boleh mengubahnya (lihat update()).
     */
    public function show(): View
    {
        $pricePerKm = (float) DeliverySetting::get('price_per_km', 3000);

        return view('delivery-settings.index', compact('pricePerKm'));
    }

    public function update(UpdateDeliverySettingRequest $request): RedirectResponse
    {
        $this->deliveryTaskService->updateTariff((float) $request->validated('price_per_km'));

        return back()->with('success', 'Tarif berhasil diperbarui.');
    }
}
