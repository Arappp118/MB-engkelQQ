<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_booking', 30)->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('waktu');
            $table->text('keluhan');
            $table->text('diagnosis_customer')->nullable();
            $table->enum('jenis_layanan', ['medical_checkup', 'service_rutin', 'perbaikan'])->default('medical_checkup');
            $table->boolean('pickup_requested')->default(false);
            $table->text('alamat_pickup')->nullable();
            $table->decimal('estimated_distance_km', 8, 2)->nullable();
            $table->enum('status', [
                'pending',
                'confirmed',
                'waiting_pickup',
                'vehicle_picked_up',
                'waiting_service',
                'assigned',
                'in_service',
                'service_completed',
                'waiting_payment',
                'paid',
                'ready_for_delivery',
                'completed',
                'cancelled',
            ])->default('pending');
            $table->text('catatan')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('vehicle_id');
            $table->index('status');
            $table->index('tanggal');
            $table->index('nomor_booking');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
