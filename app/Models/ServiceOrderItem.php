<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_order_id', 'service_item_id',
        'item_name_snapshot', 'price_snapshot',
        'quantity', 'subtotal',
    ];

    protected $casts = [
        'price_snapshot' => 'float',
        'quantity'       => 'integer',
        'subtotal'       => 'float',
    ];

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }
}
