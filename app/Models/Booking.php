<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor_booking', 'customer_id', 'vehicle_id', 'tanggal', 'waktu',
        'keluhan', 'diagnosis_customer', 'jenis_layanan',
        'pickup_requested', 'alamat_pickup', 'estimated_distance_km',
        'status', 'catatan', 'cancellation_reason',
    ];

    protected $casts = [
        'tanggal'           => 'date',
        'pickup_requested'  => 'boolean',
        'estimated_distance_km' => 'float',
    ];

    // Valid status transitions
    public const STATUS_TRANSITIONS = [
        'pending'              => ['confirmed', 'cancelled'],
        'confirmed'            => ['waiting_pickup', 'waiting_service', 'cancelled'],
        'waiting_pickup'       => ['vehicle_picked_up', 'cancelled'],
        'vehicle_picked_up'    => ['waiting_service'],
        'waiting_service'      => ['assigned'],
        'assigned'             => ['in_service'],
        'in_service'           => ['service_completed'],
        'service_completed'    => ['waiting_payment'],
        'waiting_payment'      => ['paid'],
        'paid'                 => ['ready_for_delivery', 'completed'],
        'ready_for_delivery'   => ['completed'],
        'completed'            => [],
        'cancelled'            => [],
    ];

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::STATUS_TRANSITIONS[$this->status] ?? []);
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function serviceOrder()
    {
        return $this->hasOne(ServiceOrder::class);
    }

    public function deliveryTasks()
    {
        return $this->hasMany(DeliveryTask::class);
    }

    public function pickupTask()
    {
        return $this->hasOne(DeliveryTask::class)->where('type', 'pickup');
    }

    public function deliveryTask()
    {
        return $this->hasOne(DeliveryTask::class)->where('type', 'delivery');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['completed', 'cancelled']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public static function generateNomorBooking(): string
    {
        $prefix = 'MC';
        $date   = now()->format('Ymd');
        $last   = static::where('nomor_booking', 'like', "{$prefix}{$date}%")->count() + 1;
        return sprintf('%s%s%04d', $prefix, $date, $last);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'             => 'Menunggu Konfirmasi',
            'confirmed'           => 'Dikonfirmasi',
            'waiting_pickup'      => 'Menunggu Pickup',
            'vehicle_picked_up'   => 'Kendaraan Dijemput',
            'waiting_service'     => 'Menunggu Servis',
            'assigned'            => 'Mekanik Ditugaskan',
            'in_service'          => 'Sedang Diservis',
            'service_completed'   => 'Servis Selesai',
            'waiting_payment'     => 'Menunggu Pembayaran',
            'paid'                => 'Dibayar',
            'ready_for_delivery'  => 'Siap Diambil',
            'completed'           => 'Selesai',
            'cancelled'           => 'Dibatalkan',
            default               => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'            => 'yellow',
            'confirmed'          => 'blue',
            'waiting_pickup'     => 'orange',
            'vehicle_picked_up'  => 'indigo',
            'waiting_service'    => 'purple',
            'assigned'           => 'cyan',
            'in_service'         => 'violet',
            'service_completed'  => 'lime',
            'waiting_payment'    => 'amber',
            'paid'               => 'green',
            'ready_for_delivery' => 'teal',
            'completed'          => 'emerald',
            'cancelled'          => 'red',
            default              => 'gray',
        };
    }
}
