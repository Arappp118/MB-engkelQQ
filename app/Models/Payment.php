<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_order_id', 'payment_method', 'amount',
        'proof_path', 'paid_at', 'status',
        'verified_by', 'verified_at', 'notes',
    ];

    protected $casts = [
        'amount'      => 'float',
        'paid_at'     => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash'     => 'Tunai',
            'transfer' => 'Transfer Bank',
            'qris'     => 'QRIS',
            default    => $this->payment_method,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'              => 'Menunggu',
            'waiting_verification' => 'Menunggu Verifikasi',
            'verified'             => 'Terverifikasi',
            'rejected'             => 'Ditolak',
            'paid'                 => 'Lunas',
            default                => $this->status,
        };
    }
}
