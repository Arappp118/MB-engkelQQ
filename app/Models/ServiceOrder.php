<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'mechanic_id', 'diagnosis_mechanic', 'notes',
        'status', 'started_at', 'completed_at',
        'subtotal', 'delivery_fee', 'grand_total',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'subtotal'     => 'float',
        'delivery_fee' => 'float',
        'grand_total'  => 'float',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function mechanic()
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    public function items()
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Recalculate totals from items. Always called server-side.
     * NEVER trust amounts from frontend.
     */
    public function recalculate(): void
    {
        $this->subtotal    = $this->items()->sum('subtotal');
        $this->grand_total = $this->subtotal + $this->delivery_fee;
        $this->save();
    }

    public function generateInvoiceNumber(): string
    {
        return 'INV-' . str_pad($this->id, 8, '0', STR_PAD_LEFT);
    }
}
