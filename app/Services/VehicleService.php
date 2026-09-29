<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class VehicleService
{
    /**
     * Buat kendaraan baru milik user tertentu.
     */
    public function create(array $data, User $owner): Vehicle
    {
        return DB::transaction(function () use ($data, $owner) {
            $vehicle = Vehicle::create([
                ...$data,
                'user_id' => $owner->id,
            ]);

            AuditLog::record('vehicle.created', 'vehicle', $vehicle->id, [
                'nomor_polisi' => $vehicle->nomor_polisi,
                'owner_id'     => $owner->id,
            ]);

            return $vehicle;
        });
    }

    /**
     * Update data kendaraan.
     */
    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        return DB::transaction(function () use ($vehicle, $data) {
            $before = $vehicle->only(array_keys($data));

            $vehicle->update($data);

            AuditLog::record('vehicle.updated', 'vehicle', $vehicle->id, [
                'before' => $before,
                'after'  => $vehicle->only(array_keys($data)),
            ]);

            return $vehicle->fresh();
        });
    }

    /**
     * Hapus kendaraan, dengan validasi tidak ada booking aktif.
     *
     * @throws \RuntimeException
     */
    public function delete(Vehicle $vehicle): void
    {
        if ($this->hasActiveBooking($vehicle)) {
            throw new \RuntimeException('Kendaraan tidak bisa dihapus karena masih ada booking aktif.');
        }

        DB::transaction(function () use ($vehicle) {
            AuditLog::record('vehicle.deleted', 'vehicle', $vehicle->id, [
                'nomor_polisi' => $vehicle->nomor_polisi,
            ]);

            $vehicle->delete();
        });
    }

    /**
     * Cek apakah kendaraan masih punya booking yang berjalan.
     */
    public function hasActiveBooking(Vehicle $vehicle): bool
    {
        return $vehicle->bookings()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();
    }

    /**
     * Ambil daftar kendaraan sesuai role user (admin lihat semua, customer lihat miliknya).
     */
    public function listFor(User $user, int $perPage = 15)
    {
        return $user->isAdmin()
            ? Vehicle::with('owner')->latest()->paginate($perPage)
            : $user->vehicles()->latest()->paginate($perPage);
    }
}
