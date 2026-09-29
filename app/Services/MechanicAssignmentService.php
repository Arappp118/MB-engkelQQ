<?php

namespace App\Services;

use App\Models\User;
use App\Models\Booking;

class MechanicAssignmentService
{
    /**
     * Automatically assign the best available mechanic based on:
     * 1. Vehicle type / engine type → specialization match
     * 2. Problem type (from keluhan keywords)
     * 3. Mechanic is active
     * 4. Mechanic has lowest current workload
     */
    public function assign(Booking $booking): ?User
    {
        $vehicle = $booking->vehicle;
        $specialization = $this->determineSpecialization($vehicle->tipe_mesin, $booking->keluhan);

        // Primary: exact specialization match
        $mechanic = $this->findAvailableMechanic($specialization);

        // Fallback: any active mechanic with lowest workload
        if (!$mechanic) {
            $mechanic = $this->findAnyAvailableMechanic();
        }

        return $mechanic;
    }

    private function determineSpecialization(string $tipeMesin, string $keluhan): string
    {
        // Keyword-based override for electrical problems
        $electricalKeywords = ['listrik', 'kelistrikan', 'aki', 'lampu', 'ecu', 'starter', 'kabel', 'dynamo'];
        foreach ($electricalKeywords as $keyword) {
            if (stripos($keluhan, $keyword) !== false) {
                return 'mekanik_kelistrikan';
            }
        }

        return match($tipeMesin) {
            '2_tak'   => 'mekanik_2_tak',
            '4_tak'   => 'mekanik_4_tak',
            'listrik' => 'mekanik_kelistrikan',
            default   => 'mekanik_4_tak',
        };
    }

    private function findAvailableMechanic(string $specialization): ?User
    {
        return User::where('role', 'mechanic')
            ->where('specialization', $specialization)
            ->where('is_active', true)
            ->withCount(['serviceOrders as active_jobs' => function ($q) {
                $q->whereIn('status', ['pending', 'in_progress']);
            }])
            ->orderBy('active_jobs', 'asc')
            ->first();
    }

    private function findAnyAvailableMechanic(): ?User
    {
        return User::where('role', 'mechanic')
            ->where('is_active', true)
            ->withCount(['serviceOrders as active_jobs' => function ($q) {
                $q->whereIn('status', ['pending', 'in_progress']);
            }])
            ->orderBy('active_jobs', 'asc')
            ->first();
    }
}
