<?php

namespace App\Services;

use App\Models\DeliverySetting;

class DeliveryFeeService
{
    /**
     * BR-007: Tarif ongkir berasal dari database/config.
     * BR-008: Courier tidak boleh mengubah tarif ongkir.
     * BR-014: Total transaksi selalu dihitung ulang oleh backend.
     */
    public function getPricePerKm(): float
    {
        return (float) DeliverySetting::get('price_per_km', 3000);
    }

    public function calculate(float $distanceKm): float
    {
        $pricePerKm = $this->getPricePerKm();
        $raw = $distanceKm * $pricePerKm;
        // Round up to nearest 500
        return ceil($raw / 500) * 500;
    }
}
