<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'courier_id', 'type', 'address',
        'distance_km', 'delivery_fee', 'status',
        'started_at', 'completed_at', 'notes',
    ];

    protected $casts = [
        'distance_km'  => 'float',
        'delivery_fee' => 'float',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'pickup' ? 'Penjemputan' : 'Pengantaran';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'     => 'Menunggu',
            'assigned'    => 'Ditugaskan',
            'in_progress' => 'Dalam Perjalanan',
            'completed'   => 'Selesai',
            'cancelled'   => 'Dibatalkan',
            default       => $this->status,
        };
    }
}
