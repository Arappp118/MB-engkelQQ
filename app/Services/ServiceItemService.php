<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ServiceItem;
use Illuminate\Support\Facades\DB;

class ServiceItemService
{
    /**
     * Buat master jasa/sparepart baru. Harga hanya disimpan dari data
     * yang sudah divalidasi Form Request (server-side), bukan dari sumber lain.
     */
    public function create(array $data): ServiceItem
    {
        return DB::transaction(function () use ($data) {
            $item = ServiceItem::create($data);

            AuditLog::record('service_item.created', 'service_item', $item->id, [
                'name'  => $item->name,
                'price' => $item->price,
                'is_sparepart' => $item->is_sparepart,
            ]);

            return $item;
        });
    }

    /**
     * Update data master, termasuk harga. Method ini HANYA boleh dipanggil
     * lewat jalur yang sudah dijaga Policy (admin only) di Controller/Form Request.
     */
    public function update(ServiceItem $item, array $data): ServiceItem
    {
        return DB::transaction(function () use ($item, $data) {
            $priceChanged = isset($data['price']) && (float) $data['price'] !== (float) $item->price;
            $before = $item->only(array_keys($data));

            $item->update($data);

            AuditLog::record('service_item.updated', 'service_item', $item->id, [
                'before' => $before,
                'after'  => $item->only(array_keys($data)),
            ]);

            if ($priceChanged) {
                AuditLog::record('service_item.price_changed', 'service_item', $item->id, [
                    'old_price' => $before['price'] ?? null,
                    'new_price' => $item->price,
                ]);
            }

            return $item->fresh();
        });
    }

    /**
     * Update stok secara eksplisit (terpisah dari update umum agar audit trail jelas).
     */
    public function updateStock(ServiceItem $item, int $newStock): ServiceItem
    {
        return DB::transaction(function () use ($item, $newStock) {
            $oldStock = $item->stock;

            $item->update(['stock' => $newStock]);

            AuditLog::record('service_item.stock_changed', 'service_item', $item->id, [
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
            ]);

            return $item->fresh();
        });
    }

    /**
     * Nonaktifkan item (soft-disable, bukan delete permanen — item lama
     * di transaksi historis tetap valid karena pakai price snapshot).
     */
    public function deactivate(ServiceItem $item): void
    {
        DB::transaction(function () use ($item) {
            $item->update(['is_active' => false]);

            AuditLog::record('service_item.deactivated', 'service_item', $item->id, [
                'name' => $item->name,
            ]);
        });
    }

    public function listFor(string $specialization = null, bool $activeOnly = true, int $perPage = 20)
    {
        $query = ServiceItem::query();

        if ($activeOnly) {
            $query->active();
        }

        if ($specialization) {
            $query->forSpecialization($specialization);
        }

        return $query->latest()->paginate($perPage);
    }
}
